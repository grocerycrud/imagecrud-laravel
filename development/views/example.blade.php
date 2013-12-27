@extends('layout')

@section('js_files')
	<?php foreach ($js_files as $file) { ?>
	    <script src="<?php echo $file; ?>"></script>
	<?php } ?>
@stop

@section('css_files')
	<?php foreach ($css_files as $file) { ?>
	    <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />

	<?php } ?>
@stop

@section('content')
    <?php echo $output; ?>
@stop