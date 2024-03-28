# opz_nav

An emlog plugin that is compatible with opz_nav template (AI资源网址导航模版).
But its capabilities can also be used by other templates

## install

Plugin homepage: https://www.emlog.net/plugin/detail/614

Search in the app store for: 网址导航插件

This is a free plugin, just click on the free installation button

## Examples

module.php in template
```php
if (!function_exists('get_link_url')) {

    // 获取跳转链接
    function get_link_url ($gid) {
        if (function_exists('_opz_url') && _opz_url($gid)) {
            return _opz_url($gid);
        }
        return BLOG_URL;
    }
}
```

## author

https://blog.phpat.com

## more templates/plugins

https://www.emlog.net/author/index/74