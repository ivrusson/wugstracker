/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);

const searchBy = async (e, state, dispatch) => {
    e.preventDefault();
    const { pagination } = state;
    const { limit, skip, orderBy } = pagination;

    let search = document.getElementById('searchInput').value.trim();

    if (!search || search === '' || search.length <= 4) return;

    dispatch({ type: 'loading', payload: true });
    let logsResult = await WugsApi.fetch({
        method: 'GET',
        path: 'logs',
        query: {
            limit,
            skip,
            orderBy
        },
        search
    });
    if (logsResult) {
        dispatch({ type: 'set_search', payload: search });
        dispatch({ type: 'set_logs', payload: logsResult });
        dispatch({ type: 'set_page', payload: 1 });
    }
};

const changeLimit = async (e, limit, state, dispatch) => {
    e.preventDefault();
    const { pagination } = state;
    const { orderBy, skip } = pagination;

    dispatch({ type: 'loading', payload: true });
    let logsResult = await WugsApi.fetch({
        method: 'GET',
        path: 'logs',
        query: {
            limit,
            skip,
            orderBy
        }
    });
    if (logsResult) {
        dispatch({ type: 'set_logs', payload: logsResult });
        dispatch({ type: 'set_page', payload: 1 });
    }
};

const changeOrderBy = async (e, orderBy, state, dispatch) => {
    e.preventDefault();
    const { pagination } = state;
    const { limit, skip } = pagination;

    dispatch({ type: 'loading', payload: true });
    let logsResult = await WugsApi.fetch({
        method: 'GET',
        path: 'logs',
        query: {
            limit,
            skip,
            orderBy
        }
    });
    if (logsResult) {
        dispatch({ type: 'set_logs', payload: logsResult });
        dispatch({ type: 'set_page', payload: 1 });
    }
};

const reset = async (e, state, dispatch) => {
    e.preventDefault();
    const { pagination } = state;
    const { limit, skip, orderBy } = pagination;

    dispatch({ type: 'loading', payload: true });
    dispatch({ type: 'set_search', payload: null });
    document.getElementById('searchInput').value = '';
    let logsResult = await WugsApi.fetch({
        method: 'GET',
        path: 'logs',
        query: {
            limit,
            skip,
            orderBy
        }
    });
    if (logsResult) {
        dispatch({ type: 'set_logs', payload: logsResult });
        dispatch({ type: 'set_page', payload: 1 });
    }
};

const refresh = async (e, state, dispatch) => {
    e.preventDefault();
    const { pagination } = state;
    const { currentPage, limit, orderBy, total } = pagination;

    dispatch({ type: 'loading', payload: true });
    let logsResult = await WugsApi.fetch({
        method: 'GET',
        path: 'logs',
        query: {
            limit,
            skip: (currentPage - 1) * limit,
            orderBy
        }
    });
    if (logsResult) {
        dispatch({ type: 'set_logs', payload: logsResult });
    }
};

const perPageList = [
    { title: '5 items', limit: 5 },
    { title: '10 items', limit: 10 },
    { title: '25 items', limit: 25 },
    { title: '50 items', limit: 50 },
]

const orderByList = [
    { title: 'Newest dates', orderBy: { updated_at: 'desc' } },
    { title: 'Oldest dates', orderBy: { updated_at: 'asc' } }
]

export const Menu = ({ state, dispatch }) => {
    const { pagination, search } = state;

    let currentorderBy = 'Page limit';
    let currentLimit = 'Sort by';

    perPageList.forEach(item => {
        if (JSON.stringify(item.limit) === JSON.stringify(pagination.limit)) currentLimit = item.title;
    });
    orderByList.forEach(item => {
        if (JSON.stringify(item.orderBy) === JSON.stringify(pagination.orderBy)) currentorderBy = item.title;
    });

    let title = html`<a class="navbar-brand">The system already tracks <span class="text-secondary">${pagination.total}</span> issues.</a>`;
    if (search) {
        title = html`<a class="navbar-brand">Search results for <span class="text-warning">${search}</span> with <span class="text-secondary">${pagination.total}</span> issues.</a>`;
    }
    return html`
        <nav class="navbar navbar-light" style="background-color:transparent;">
            <div class="container-fluid" style="padding: 0;">
                ${title}
                <div class="d-flex">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle btn-sm" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            Show: <b>${currentLimit}</b>
                        </button>
                        <ul class="dropdown-menu">
                            ${perPageList.map(item => html`<li><a class="dropdown-item" role="button" onclick=${(e)=> changeLimit(e, item.limit, state, dispatch)}>${item.title}</a></li>`)}
                        </ul>
                    </div>
                    <div style="width:10px;">
                        </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-expanded="false">
                            Sort by: <b>${currentorderBy}</b>
                        </button>
                        <ul class="dropdown-menu">
                            ${orderByList.map(item => html`<li><a class="dropdown-item" role="button" onclick=${(e) => changeOrderBy(e, item.orderBy, state, dispatch)}>${item.title}</a></li>`)}
                        </ul>
                    </div>
                    <div style="width:10px;"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick=${(e) => reset(e, state, dispatch)}><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                    <div style="width:10px;"></div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick=${(e) => refresh(e, state, dispatch)}><i class="bi bi-arrarow-repeat"></i> Refresh</button>
                    <div style="width:10px;"></div>
                    <form class="d-flex" onsubmit=${(e) => { e.preventDefault(); searchBy(e, state, dispatch); }} action="" style="min-width:400px">
                        <div class="input-group">
                            <input id="searchInput" name="searchInput" type="text" class="form-control" placeholder="Search errors by texting something..."/>
                            <button class="btn btn-outline-secondary" type="button" style="border-color:#ced4da;transform: translateX(1px);" onclick=${(e) => searchBy(e, state, dispatch)}><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </nav>
    `;
}