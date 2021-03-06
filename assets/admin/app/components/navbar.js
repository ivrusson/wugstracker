/**
* WugsTracker Admin
*/

const html = htm.bind(preact.h);


const WPAdminMenu = () => {
    const { home_url, wp_admin_menu = [] } = wugs_data;
    
    return html`
    <div class="dropend">
        <a class="navbar-brand dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="dashicons dashicons-wordpress-alt" style="margin: 5px;"></span>
        </a>
        <ul class="dropdown-menu">
            ${wp_admin_menu.map((item, index) => {
                if (item[0] === '') {
                    return html`<li><hr class="dropdown-divider" /></li>`;
                } else {
                    let title = item[0].split('<span')[0];
                    let url = `${home_url}/wp-admin/${item[2]}`;
                    let icon = item[6].indexOf('data:image/') !== -1 ? html`<div class="wp-menu-image svg" style="background-image: url('${item[6]}');"></div>` : html`<span class="dashicons ${item[6]}"></span>`;
                    return html`<li><a class="dropdown-item" href="${url}">${icon}${title}</a></li>`;
                }
            })}
        </ul>
    </div>    
    `;
};

export const Navbar = ({}) => {

    return html`
        <div>
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <${WPAdminMenu} />
                    <a class="navbar-brand" href="${wugs_data.home_url}">
                        <b>Wugs</b><b style="color:red;">Tracker</b>
                    </a>
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link ${wugs_data.current === 'tracker' ? 'active' : ''}" href="${wugs_data.current !== 'tracker' ? wugs_data.home_url +'/wp-admin/admin.php?page=wugstracker-admin-tracker' : '#'}">Tracker</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link ${wugs_data.current === 'configuration' ? 'active' : ''}" href="${wugs_data.current !== 'configuration' ? wugs_data.home_url + '/wp-admin/admin.php?page=wugstracker-admin-options' : '#'}">Configuration</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    `;
}