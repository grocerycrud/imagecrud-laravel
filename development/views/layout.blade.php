<html>
<head>
    @yield('js_files')
    @yield('css_files')
</head>
<body>
    <div>
        <a href='<?php echo URL::to('/example/example1'); ?>'>Example 1 - Simple</a> |
        <a href='<?php echo URL::to('/example/example2'); ?>'>Example 2 - With ordering</a> |
        <a href='<?php echo URL::to('/example/example3/22'); ?>'>Example 3 - With category id</a> |
        <a href='<?php echo URL::to('/example/example4'); ?>'>Example 4 - Images with editable title</a> |
        <a href='<?php echo URL::to('/example/example5'); ?>'>Example 5 - Photo Gallery without uploader</a>
    </div>
    <br/><br/>
    @yield('content')
</body>
</html>