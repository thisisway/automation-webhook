<?php
namespace Kernel;
use Kernel\Server;
use Dompdf\Dompdf;

trait Response{

    public $pageName = '';
    public $defaultLayout = 'Layout.php';
    public $enableDefaultLayout = true;
    public $modules = [
        'datatable'  => false,
        'errors'     => true,
        'success'    => true,
        'helpers'    => true,
        'ifIsset'    => true,
    ];

    
    public function View($path_to_view, $args = [])
    {
        extract($args);
        $this->loadModules();
        if($this->enableDefaultLayout)
        {
            $file = Server::getRealBasePath().'/resources/views/'.$path_to_view.'.php';
            if(file_exists($file))
            {
                $slot = $file;                              
                include_once(Server::getRealBasePath().'/resources/views/layout.php');
            }
        }else{
            include_once(Server::getRealBasePath().'/resources/views/'.$path_to_view.'.php');
        }
    }

    public function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data); 
        die();
    }

    public function enableModule($module)
    {
        $this->modules[$module] = true;
        return $this;
    }

    public function enableModules($modules = [])
    {
        foreach($modules as $module){
            $this->modules[$module] = true;
        }
    }

    public function disableModules($modules)
    {
        foreach($modules as $module){
            $this->modules[$module] = false;
        }
    }

    public function module($module){
        return $this->modules[$module];
    }

    private function loadModules()
    {
        $folderModules = scandir(Server::getRealBasePath().'/resources/modules/');
        //load extra modules
        foreach($folderModules as $module){
            if(str_contains($module, '.php'))
                include_once(Server::getRealBasePath().'/resources/modules/'.$module);
        }
    }

    public function pdf($path_to_html, $title, $args = [], $orientation = 'portrait', $debug = false)
    {
        $file = Server::getRealBasePath().'/resources/documents/'.$path_to_html.'.php';
        
        if(file_exists($file))
        { 
            $dompdf = new Dompdf();
            $htmlEntries = $this->htmlContent($file, $args);

            if($debug) {
                echo $htmlEntries;
                die();
            }

            $dompdf->loadHtml($htmlEntries);
            $dompdf->setPaper('A4', $orientation);
            $dompdf->render();
            $dompdf->stream($title.".pdf",["Attachment" => false]);
        }
        
    }

    private function htmlContent($file, $args = [])
    {   
        extract($args);
        ob_start();
        include $file;
        return ob_get_clean();
    }
}