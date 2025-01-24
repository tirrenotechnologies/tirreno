<?php

/*

	Copyright (c) 2009-2019 F3::Factory/Bong Cosca, All rights reserved.

	This file is part of the Fat-Free Framework (http://fatfreeframework.com).

	This is free software: you can redistribute it and/or modify it under the
	terms of the GNU General Public License as published by the Free Software
	Foundation, either version 3 of the License, or later.

	Fat-Free Framework is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	General Public License for more details.

	You should have received a copy of the GNU General Public License along
	with Fat-Free Framework.  If not, see <http://www.gnu.org/licenses/>.

*/

//! Cache-based session handler
class Session extends Magic {

	protected
		//! Session ID
		$sid,
		//! Anti-CSRF token
		$_csrf,
		//! User agent
		$_agent,
		//! IP,
		$_ip,
		//! Suspect callback
		$onsuspect,
		//! Cache instance
		$_cache,
		//! Session meta data
		$_data=[];

	/**
	*	Open session
	*	@return TRUE
	*	@param $path string
	*	@param $name string
	**/
	function open(string $path, string $name): bool
    {
		return TRUE;
	}

	/**
	*	Close session
	*	@return TRUE
	**/
	function close(): bool
    {
		$this->sid=NULL;
		$this->_data=[];
		return TRUE;
	}

	/**
	*	Return session data in serialized format
	*	@return false|string
	*	@param $id string
	**/
	#[ReturnTypeWillChange]
    function read(string $id)
    {
		$this->sid=$id;
		if (!$data=$this->_cache->get($id.'.@'))
			return '';
		$this->_data = $data;
		if ($data['ip']!=$this->_ip || $data['agent']!=$this->_agent) {
			$fw=Base::instance();
			if (!isset($this->onsuspect) ||
				$fw->call($this->onsuspect,[$this,$id])===FALSE) {
				//NB: `session_destroy` can't be called at that stage (`session_start` not completed)
				$this->destroy($id);
				$this->close();
				unset($fw->{'COOKIE.'.session_name()});
				$fw->error(403);
			}
		}
		return $data['data'];
	}

	/**
	*	Write session data
	*/
	function write(string $id, string $data): bool
    {
		$fw=Base::instance();
		$jar=$fw->JAR;
		$this->_cache->set($id.'.@',
			[
				'data'=>$data,
				'ip'=>$this->_ip,
				'agent'=>$this->_agent,
				'stamp'=>time()
			],
			$jar['expire']
		);
		return TRUE;
	}

	/**
	*	Destroy session
	*/
	function destroy(string $id): bool
    {
		$this->_cache->clear($id.'.@');
		return TRUE;
	}

	/**
	*	Garbage collector
	**/
    #[ReturnTypeWillChange]
    function gc(int $max_lifetime)
    {
		$this->_cache->reset('.@',$max_lifetime);
		return TRUE;
	}

	/**
	 *	Return session id (if session has started)
	 *	@return string|NULL
	 **/
	function sid() {
		return $this->sid;
	}

	/**
	 *	Return anti-CSRF token
	 *	@return string
	 **/
	function csrf() {
		return $this->_csrf;
	}

	/**
	 *	Return IP address
	 *	@return string
	 **/
	function ip() {
		return $this->_ip;
	}

	/**
	 *	Return Unix timestamp
	 *	@return string|FALSE
	 **/
	function stamp() {
		if (!$this->sid)
			session_start();
		return $this->_cache->exists($this->sid.'.@',$data)?
			$data['stamp']:FALSE;
	}

	/**
	 *	Return HTTP user agent
	 *	@return string
	 **/
	function agent() {
		return $this->_agent;
	}

	/**
	*	Instantiate class
	*	@param $onsuspect callback
	*	@param $key string
	**/
	function __construct($onsuspect=NULL,$key=NULL,$cache=null) {
		$this->onsuspect=$onsuspect;
		$this->_cache=$cache?:Cache::instance();
        if (version_compare(PHP_VERSION, '8.4.0')>=0) {
            // TODO: remove this when php7 support is dropped
            session_set_save_handler(new SessionAdapter($this));
        } else {
            session_set_save_handler(
                [$this,'open'],
                [$this,'close'],
                [$this,'read'],
                [$this,'write'],
                [$this,'destroy'],
                [$this,'gc']
            );
        }
		register_shutdown_function('session_commit');
		$fw=\Base::instance();
		$headers=$fw->HEADERS;
		$this->_csrf=$fw->hash($fw->SEED.
			extension_loaded('openssl')?
				implode(unpack('L',openssl_random_pseudo_bytes(4))):
				mt_rand()
			);
		if ($key)
			$fw->$key=$this->_csrf;
		$this->_agent=isset($headers['User-Agent'])?$headers['User-Agent']:'';
		$this->_ip=$fw->IP;
	}

	/**
	 * check latest meta data existence
	 * @param string $key
	 * @return bool
	 */
	function exists($key) {
		return isset($this->_data[$key]);
	}

	/**
	 * get meta data from latest session
	 * @param string $key
	 * @return mixed
	 */
	function &get($key) {
		return $this->_data[$key];
	}

	function set($key,$val) {
		trigger_error('Unable to set data on previous session');
	}

	function clear($key) {
		trigger_error('Unable to clear data on previous session');
	}
}
