<?php
namespace core;

abstract class controller
{
	public function render($template, $data = [], $layout = 'index')
	{
		extract($data);
		ob_start();

		include realpath('app/views/'.$template.'.php');
		$content = ob_get_contents();
		ob_end_clean();

		include_once realpath('app/views/layout/'.$layout.'.php');
	}

    public function renderPartial($template, $data = [])
    {
        extract($data);
        ob_start();

        include realpath('app/views/'.$template.'.php');
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

	public function redirect($url)
	{
		header('Location: '.$url);
	}
}