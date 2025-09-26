# tirreno changelog

## tirreno v0.9.9

* user activity sparklines charts
* sessions stat collection
* settings for `LOGBOOK_LIMIT`
* settings for `ALLOW_FORGOT_PASSWORD`
* unique UA on language change
* suspicious url substrings update
* textarea url/query rendering fixes
* default current time on invalid eventTime in sensor and 0 on invalid httpCode
* default N/A on empty userName
* jquery-autocomplete update
* minor bug fixes

## tirreno v0.9.8

* field audit trail via new event type `field_edit`
* optional payload for event types `page_search` and `account_email_change`
* sequential load of inner pages elements
* inactive rules visualisation
* extended list for user-agent vulnerability check
* several rules tuning
* Fat-Free Framework update
* minor bug fixes

## tirreno v0.9.7

* DataTables 2.3.2
* different type for event bars charts
* live clock
* new `page_error` event type
* chart for logbook page
* save server time zone timestamp for logbook instead of UTC
* /assets dir for logs, custom rules and suspicious words lists
* prevent parallel requests on daterange switch and rule play
* use only DOM API instead of innerHTML
* improved counties grid and map queries
* fix timestamps localization in panels
* enhanced .htaccess

## tirreno v0.9.6

* flexible rule system
* heroku automated deployment
* improved main page load
* sorting columns whitelists for prevention of ajax blind sql injections
* blacklist chart improvements + chart class reorganization
* add `B23` and `B24` rules
* device detector updates
* /config/local dir for custom configs and extensions

## tirreno v0.9.5

* new search filters for blacklist and users
* review queue and automated blacklisting thresholds
* device detector update
* database updates module
* improved notifications
* default 1D range for grids
* blacklist API

## tirreno v0.9.4

* new search filters for types of events and IPs
* failed login attempt widget for the dashboard screen
* inactive session limit
* new chart for events
* clickable rules
* highlighting reviewed users
* js improvements
* minor bug fixes
* lint configurations
* [wip] review queue threshold settings

## tirreno v0.9.3

* user graph on /event plot
* clickable countries on svg map
* improve /blacklist management
* index.php in all subdirs to prevent directory listing
* force utf-8 sensor input

## tirreno v0.9.2

* XSS vulnerability patch
* optimize getLastEvent()

## tirreno v0.9.1

* data plotting with hour and minute resolution
* minor bug fixes
* dependencies update
* js linting improvements
* logbook search fix

## tirreno v0.9.0

* initial release
