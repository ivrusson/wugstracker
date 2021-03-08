

export const initialState = {
    loading: true,
    logs: [],
    pagination: {
        currentPage: 1,
        limit: 5,
        skip: 0,
        total: 0,
        total_results: 0,
        orderBy: { updated_at: 'desc'}
    },
    filters: {},
    search: null,
    messages: []
};

export const reducer = (state, action) => {
    switch (action.type) {
        case 'loading':
            return {
                ...state,
                loading: action.payload
            };
        case 'set_logs':
            const { results, total, orderBy, limit, skip } = action.payload;
            return {
                ...state,
                logs: results,
                pagination: {
                    ...state.pagination,
                    limit,
                    skip,
                    orderBy,
                    total
                },
                loading: false
            };
        case 'set_search':
            return {
                ...state,
                search: action.payload
            };
        case 'set_page':
            return {
                ...state,
                pagination: {
                    ...state.pagination,
                    currentPage: action.payload
                }
            };
        case 'set_search':
            let search = action.payload;
            return {
                ...state,
                search
            };
        case 'set_messages':
            let messages = action.payload;
            return {
                ...state,
                messages
            };
        case 'remove_log':
            let logs = state.logs.filter((item) => item._id !== action.payload);
            return {
                ...state,
                logs,
                pagination: {
                    ...state.pagination,
                    total_results: state.pagination.total_results - 1,
                    total: state.pagination.total-1
                }
            };
        default:
            throw new Error();
    }
};