

export const initialState = {
    loading: true,
    options: {},
    messages: []
};

export const reducer = (state, action) => {
    switch (action.type) {
        case 'loading':
            return {
                ...state,
                loading: action.payload
            };
        case 'set_options':
            let options = action.payload;
            return {
                ...state,
                options: {
                    ...state.options,
                    ...options
                },
                loading: false
            };
        case 'set_messages':
            let messages = action.payload;
            return {
                ...state,
                messages
            };
        case 'remove_messages':
            return {
                ...state,
                messages: {}
            };
        default:
            throw new Error();
    }
};