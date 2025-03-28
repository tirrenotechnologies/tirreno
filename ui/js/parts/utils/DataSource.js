const getQueryParams = (params) => {
    const data = {};
    Object.assign(data, params);

    if (params.dateRange) {
        data['dateTo']    = params.dateRange.dateTo;
        data['dateFrom']  = params.dateRange.dateFrom;
        data['keepDates'] = params.dateRange.keepDates;
    }

    if (params.searchValue) {
        data['search'] = {
            value: params.searchValue
        };
    }

    return data;
};

export {getQueryParams};
