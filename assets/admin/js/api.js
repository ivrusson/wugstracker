

var WugsApi = {};

WugsApi.fetch = async function ({ method, path, data, query, search }) {
    var baseUrl = wugs_data.api_url;
    
    var options = {
        method: method,
        mode: 'cors',
        cache: 'no-cache',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-Wp-Js-Debugger-Nonce': this.getCookie('wugstracker-nonce'),
            'Accept': 'application/json'
        },
        redirect: 'follow',
        referrerPolicy: 'no-referrer',
    };
    console.log(data);
    if (['POST', 'PUT', 'PATCH', 'DELETE'].indexOf(method) !== -1) {
        options.body = JSON.stringify(data)
    }
    let url = baseUrl + path + '/';
    if (query && Object.keys(query).length > 0) {
        var queryVars = Object.keys(query).map(key => {
            if (typeof query[key] === 'object') return `${key}=${JSON.stringify(query[key])}`;
            return `${key}=${JSON.stringify(query[key])}`;
        }).join('&');
        url += '?' + encodeURI(queryVars);
    }

    if (search) {
        url += '?search=' + encodeURI(search);
        if (query && Object.keys(query).length > 0) {            var queryVars = Object.keys(query).map(key => {
                if (typeof query[key] === 'object') return `${key}=${JSON.stringify(query[key])}`;
                return `${key}=${JSON.stringify(query[key])}`;
            }).join('&');
            url += '&' + encodeURI(queryVars);
        }

    }
    
    const response = await fetch(url, options)
    .then((res) => res.json())
    .then(result => {
        console.log('WugsApi fetch   result', result);
        return result.status ? result.data : null
    })
    .catch(err => {
        console.log("Error: " + err);
        return null;
    });

    return response;
};

WugsApi.getCookie = function (name) {
    var str = RegExp(name + "=[^;]+").exec(document.cookie);
    return decodeURIComponent(!!str ? str.toString().replace(/^[^=]+./, "") : "");
};

