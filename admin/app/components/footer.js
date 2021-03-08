/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);

export const Footer = ({ }) => {

    return html`
        <div style="margin-top:50px;">
            <p class="text-center" style="margin:0;">
                <small>
                    <span style="margin-right: 5px;">Plugin created by</span>
                    <a href="https://github.com/ivrusson" target="_blank" style="text-decoration: unset;">
                        <img src="https://avatars.githubusercontent.com/u/3583352?s=20&v=4" class="rounded-circle" height="20"
                            width="20" />
                        <em style="color:#b6477a;margin-left:3px;">Ivrusson</em>
                    </a>
                </small>
            </p>
            <p class="text-center" style="margin:0;">
                <hr style="max-width: 400px;margin: 15px auto 5px auto;" />
            </p>
            <p class="text-center" style="margin:0;">
                <small>
                    Powered by
                </small>
            </p>
            <p class="text-center" style="margin:0;">
                <a href="https://preactjs.com/" target="_blank" title="Check Preact">
                    <img src="${wugs_data.assets_url}/images/preact_logo.png" style="width: 100px; height: auto;" />
                </a>
                <a href="https://sleekdb.github.io/" target="_blank" title="Check SleekDB">
                    <img src="${wugs_data.assets_url}/images/sleekdb_logo.png" style="width: 100px; height: auto;" />
                </a>
                <a href="https://getbootstrap.com/" target="_blank" title="Check Bootstrap 5">
                    <img src="${wugs_data.assets_url}/images/bootstrap_logo.png" style="width: 100px; height: auto;" />
                </a>
            </p>
        </div>
    `;
}