/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);
const React = preactCompat,
    ReactDOM = preactCompat;

import { Navbar } from './components/navbar.js';
import { Footer } from './components/footer.js';
import { ConfigurationApp } from './configuration/index.js';
import { TrackerApp } from './tracker/index.js';

function App() {
    return html`
        <div>
            <${Navbar} />
            <div class="container-fluid">
                ${wugs_data.current === 'tracker' ? html`<${TrackerApp}/>` : null}
                ${wugs_data.current === 'configuration' ? html`<${ConfigurationApp}/>` : null}
            </div>
            <${Footer} />
        </div>
    `;
}

document.querySelectorAll('.screen-reader-shortcut').forEach((el) => el.remove());
document.getElementById('wpadminbar').remove();
document.getElementById('adminmenuwrap').remove();
document.getElementById('wpfooter').remove();
document.getElementById('screen-meta').remove();
document.getElementById('wp-auth-check-wrap').remove();
document.body.classList.add('render');
ReactDOM.render(html`<${App}/>`, document.getElementById('root'));