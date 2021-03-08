/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);
const React = preactCompat,
    ReactDOM = preactCompat;

import { reducer, initialState } from './reducer.js';
import { Alerts } from '../components/alerts.js';
import { Container } from './components/container.js';

export function TrackerApp() {

    const [state, dispatch] = React.useReducer(reducer, initialState);
    const { loading, pagination, filters } = state;
    const { currentPage, limit, orderBy } = pagination;
    React.useEffect(async () => {
        let logsResult = await WugsApi.fetch({
            method: 'GET',
            path: 'logs',
            query: {
                limit,
                skip: (currentPage - 1) * limit,
                orderBy
            }
        });
        if (logsResult) dispatch({ type: 'set_logs', payload: logsResult });
    }, []);

    console.log('App', state);

    return html`
        <div>
            <${Alerts} messages=${state.messages} />
            <${Container} ...${{ state, dispatch }} />
        </div>
    `;
}