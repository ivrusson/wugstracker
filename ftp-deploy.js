require('dotenv').config();

const FtpDeploy = require("ftp-deploy");
const ftpDeploy = new FtpDeploy();

const config = {
    user: process.env.FTPD_USER,
    password: process.env.FTPD_PWD,
    host: process.env.FTPD_HOST,
    port: process.env.FTPD_PORT ? parseInt(process.env.FTPD_PORT) : 21,
    localRoot: __dirname + "/",
    remoteRoot: process.env.FTPD_DIR,
    include: [
        "*.php",
        "*.js",
        "*.json",
        "*.css",
        "*.md",
        "assets/**",
        "core/**",
        "includes/**"
    ],
    exclude: [
        ".env",
        ".gitignore",
        "ftp-deploy.js",
        "*.sh",
        "*.lock",
        ".git/**",
        "node_modules/**",
        "vendor/**",
    ],
    deleteRemote: true,
    forcePasv: true,
    sftp: false
};

console.log('-----------------------------------------------------')
console.log("FTP deploy start!")
console.log(`Uploading to: ${config.host}:${config.port}`)
console.log(`Into folder: ${config.remoteRoot}`)
console.log('-----------------------------------------------------')


ftpDeploy
    .deploy(config)
    .then(res => {
        console.log('-----------------------------------------------------')
        console.log("FTP deploy finished!")
        console.log('-----------------------------------------------------')
    })
    .catch(err => console.log(err));

ftpDeploy.on("uploading", function (data) {
    process.stdout.write(`Progress:  ${data.transferredFileCount}/${data.totalFilesCount} files\r`);
    if(data.transferredFileCount === data.totalFilesCount) {
        console.log(`Progress:  ${data.transferredFileCount}/${data.totalFilesCount} files\r`)
    }
});
ftpDeploy.on("upload-error", function (data) {
    console.error(data.err);
});