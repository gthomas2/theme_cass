// Example usage window.local_tlcore_req_config('local_tlcore', 'local_tlcore_vendor/corejs', 'vendorjs/corejs',
// {cdnURL: 'https://unpkg.com/core-js-bundle@3.5.0/minified', enforceDefine: false});
// We make this a global variable so that it can be called *before* define in other AMD modules.
/**
 *
 * @param frankenPlugin
 * @param registerName
 * @param jsPath
 * @param {object} options : {cdnURL: string, enforceDefine: bool}
 *
 */
window.local_tlcore_req_config = function(frankenPlugin, registerName, jsPath, options) {
    options = options ? options : {};
    var enforceDefine = options.enforceDefine ? options.enforceDefine : false;
    var cdnURL = options.cdnURL ? options.cdnURL :  null;
    var jsrev = M.cfg.jsrev;
    var path = M.cfg.wwwroot + '/local/tlcore/javascript.php/' + jsrev + '/' + frankenPlugin + '/' + jsPath;

    var paths = {};
    if (cdnURL) {
        paths[registerName] = [
            cdnURL,
            // CDN Fallback - whoop whoop!
            path
        ];
    } else {
        paths[registerName] = path;
    }
    require.config({
        enforceDefine: enforceDefine,
        paths: paths
    });
};