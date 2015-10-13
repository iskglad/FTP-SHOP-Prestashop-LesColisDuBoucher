<?php
class Tools extends ToolsCore
{
	public static function getFullPath($id_category, $end, $type_cat = 'products', Context $context = null)
	{
		if (!$context)
			$context = Context::getContext();

		$id_category = (int)$id_category;
		$pipe = (Configuration::get('PS_NAVIGATION_PIPE') ? Configuration::get('PS_NAVIGATION_PIPE') : '>');

            $default_category = 1;
            if ($type_cat === 'products')
            {
                $default_category = $context->shop->getCategory();
			$category = new Category($id_category, $context->language->id);
		}
		else if ($type_cat === 'CMS')
		    $category = new CMSCategory($id_category, $context->language->id);
		else if ($type_cat === 'Recipe')
		    $category = new RecipeCategory($id_category, $context->language->id);

		if (!Validate::isLoadedObject($category))
			$id_category = $default_category;
		if ($id_category == $default_category)
			return htmlentities($end, ENT_NOQUOTES, 'UTF-8');

		return Tools::getPath($id_category, $category->name, true, $type_cat).'<span class="navigation-pipe">'.$pipe.'</span> <span class="navigation_product">'.htmlentities($end, ENT_NOQUOTES, 'UTF-8').'</span>';
	}

    /*Debugging: Display var and exit*/
    public  static function testVar($var){
        echo '<pre>';
        print_r(">>>>Begin Tools::testVar<br/><br/>");
        print_r($var);
        echo  '</pre>';
        ddd('>>>>End Tools::testVar');
    }

    //getting the grandParent of a class
    //Allow to call its funcs
    function get_grandparent_class($thing) {
        if (is_object($thing)) {
            $thing = get_class($thing);
        }
        return get_parent_class(get_parent_class($thing));
    }

    /*Export cvs*/
    /*@data Array to right as CVS.
    /*  ie:array(array('aaa', 'bbb', 'ccc', 'dddd'),
                 array('123', '456', '789'));
    */
    public static function downloadCsv($data){
        //init vars
        $file_name = "export";
        Tools::htmlDownload($file_name);
        //$path = dirname(__FILE__).'/tmp_files/'.$file_name;

        //decode datas
        foreach ($data as &$row){
            for ($i = 0; $i < count($row); $i++){
                $row[$i] = utf8_decode(strip_tags($row[$i])); //take out htmlTags and decode to iso-8851
            }
        }

        $fp = fopen("php://output", 'w');

        foreach ($data as $line) {
            fputcsv($fp, $line);
        }

        fclose($fp);

    }

    /*htmlDownload
    /*@param $file_name: name of the file when downloading
    /*@param $format: 'csv' or 'pdf'
    /*@description: Set HTML header to force page download
    */
    public static function htmlDownload($file_name, $format = 'csv'){
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        $now = gmdate("D, d M Y H:i:s");
        header("Last-Modified: $now GMT");

        // force download
        if ($format == 'pdf')
            header("Content-type:application/pdf"); //not working
        else{
            header('Content-Type: text/csv');
        }

        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header('Content-Disposition: attachment;filename="'.$file_name.'.'.$format.'"');
        header("Content-Transfer-Encoding: binary");
    }

    /*string To Delivery Hours*/
    /*@description:
    /*Take a delivery_hours value in the lcdb_orders TABLE and make an delivery_hour array
    /*I.E: "Entre 10h00 et 11h30 ou entre 14h00 et 15h00"
            =returns=> array(
                         [0] => array('from'=>['hour'=>10, 'min'=>00], 'to'=>['hour'=>11, 'min'=>30]),
                         [1] => array('from'=>['hour'=>14, 'min'=>00], 'to'=>['hour'=>15, 'min'=>00]),
                    )
    /*@return: success : array(array('from'=>['hours', 'min']))
                error: array() //empty
    */
    public static function stringToDeliveryHoursArray($str){
        $hours = array();

        //errors : incorrect format
        if ((strlen($str) != 20 && strlen($str) !=  38)|| //size != 20 and != 38
            'Entre' != substr($str, 0, 5) || //doesnt begin by "Entre"
             strpos($str, "undefined") > 0) //Contain "undefined" keyword
            return $hours;

        //hours 1
        $from_1 = array('hour' => substr($str, 6, 2), 'min' => substr($str, 9, 2));
        $to_1 = array('hour' => substr($str, 15, 2), 'min' => substr($str, 18, 2));
        $hours[] = array('from' => $from_1, 'to'=> $to_1);

        //hours 2
        if (strpos($str, 'ou') > 0){
            $from_2 = array('hour' => substr($str, 30, 2), 'min' => substr($str, 33, 2));
            $to_2 = array('hour' => substr($str, 39, 2), 'min' => substr($str, 41, 2));
            $hours[] = array('from' => $from_2, 'to'=> $to_2);
        }

        return $hours;

    }
}?>
