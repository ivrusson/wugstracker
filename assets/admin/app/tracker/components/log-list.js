/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);

import { Loading } from '../../components/loading.js';
import { LogItem } from './log-item.js';

const paginationString = ({total, limit, currentPage}) => {
    var totalItemsCount = total;
    var numberOfItemsPerPage = limit;
    var page = currentPage;

    var numberOfPages = Math.floor((totalItemsCount + numberOfItemsPerPage - 1) / numberOfItemsPerPage);
    var start = (page * numberOfItemsPerPage) - (numberOfItemsPerPage - 1);
    var end = Math.min(start + numberOfItemsPerPage - 1, totalItemsCount);

    return `${start} to ${end} of ${totalItemsCount} items.`;
};

const goToPage = async (type, state, dispatch) => {
    const { pagination, filters } = state;
    const { currentPage, limit, skip, orderBy, total } = pagination;
    
    let toPage = null;
    if (type === 'prev') {
        if (currentPage > 1) {
            toPage = currentPage - 1;
        }
    }
    if (type === 'next') {
        let maxPage = Math.round(total / limit);
        if (currentPage < maxPage) {
            toPage = currentPage + 1;
        }
    }

    if (toPage) {
        dispatch({ type: 'loading', payload: true });
        let logsResult = await WugsApi.fetch({
            method: 'GET',
            path: 'logs',
            query: {
                limit,
                skip: (toPage-1)*limit,
                orderBy
            }
        });
        if (logsResult) {
            dispatch({ type: 'set_logs', payload: logsResult });
            dispatch({ type: 'set_page', payload: toPage });
        }
    }

    
};

export const LogList = ({ state, dispatch }) => {
    const { loading, logs, pagination } = state;
    const { currentPage, limit, total } = pagination;

    if (loading) return Loading();
    
    return html`
        <div>
            <div class="list-group">
                ${logs.map(item => LogItem({ item, dispatch }))}
            </div>
            <div class="mb-2"/>
            <nav aria-label="..." style="float:right;">
                <ul class="pagination pagination-sm">
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" role="button" aria-disabled= onclick=${() => goToPage('prev', state, dispatch)}>
                            <i class="bi bi-arrow-left"></i>
                        </a>
                    </li>
                    <li class="page-item ${currentPage === Math.ceil(total / limit) ? 'disabled' : ''}">
                        <a class="page-link" role="button" onclick=${() => goToPage('next', state, dispatch)}>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <ul style="float:right;list-style:none;display:inline-flex;margin:3px 10px 0 0;padding:0;">
                <li class="text-sm text-secondary">${paginationString({total, limit, currentPage})}</li>
            </ul>
        </div>
    `;
}