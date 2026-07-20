<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ APP_NAME }} Documentation</title>
    <link rel="icon" href="{{ secure_asset('api-docs/docs/_media/favicon.ico')  }}">
    <meta name="keywords" content="doc,docs,documentation,gitbook,creator,generator,github,jekyll,github-pages">
    <meta name="description" content="A magical documentation generator.">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="{{ secure_asset('api-docs/themes/vue.css')  }}" title="vue">
    <link rel="stylesheet" href="{{ secure_asset('api-docs/themes/dark.css')  }}" title="dark" disabled>
    <link rel="stylesheet" href="{{ secure_asset('api-docs/themes/buble.css')  }}" title="buble" disabled>
    <link rel="stylesheet" href="{{ secure_asset('api-docs/themes/pure.css')  }}" title="pure" disabled>
    <style>
        nav.app-nav li ul {
            min-width: 100px;
        }
        .cs__footer{
            display: none;
        }
    </style>
</head>
<body>
<div id="app">Loading ...</div>
</body>
<script>
    window.$docsify = {
        homepage: '{{ secure_asset('api-docs/docs/README.md') }}',
        basePath : "{{ secure_asset('api-docs/docs/') }}",
        auto2top: true,
        coverpage: true,
        executeScript: true,
        loadSidebar: true,
        loadNavbar: true,
        mergeNavbar: true,
        maxLevel: 4,
        subMaxLevel: 2,
        codesponsor: 'sddsdW',
        ga: 'UA-106147152-XAC',
        name: 'DEMAT PRO',
        formatUpdated: '{MM}/{DD} {HH}:{mm}',
    }
</script>
<script src="{{ secure_asset('api-docs/lib/docsify.js') }}"></script>
<script src="{{ secure_asset('api-docs/lib/plugins/search.js') }}"></script>
<script src="{{ secure_asset('api-docs/lib/plugins/codesponsor.js') }}"></script>
<script src="{{ secure_asset('api-docs/lib/plugins/ga.min.js') }}"></script>
<script src="//unpkg.com/prismjs/components/prism-bash.min.js"></script>
<script src="//unpkg.com/prismjs/components/prism-markdown.min.js"></script>
<script src="//unpkg.com/prismjs/components/prism-nginx.min.js"></script>
</html>
