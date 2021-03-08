/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);
const React = preactCompat;

import { reducer, initialState } from './reducer.js';
import { Loading } from '../components/loading.js';
import { Options } from './components/options.js';

export function ConfigurationApp() {

    const [state, dispatch] = React.useReducer(reducer, initialState);
    const { loading } = state;
    React.useEffect(async () => {
        let options = await WugsApi.fetch({ method: 'GET', path: 'options' });
        dispatch({ type: 'set_options', payload: options ? options : {} });
    }, []);

    return html`
        <div>
            <header class="p-2">
                <h1>Configuration page</h1>
                <p><em>Activate the options to make the magic happends!</em></p>
            </header>
            ${loading ? html`<${Loading}/>` : Options({ state, dispatch })}
        </div>
    `;
}