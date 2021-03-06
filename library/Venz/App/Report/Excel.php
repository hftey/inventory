<?php


        class Venz_App_Report_Excel {
		    
                function Venz_App_Report_Excel () {
                        $this->attr = array (
                                'exportsql'		      =>'',
                        		'data'                => null,
                                'headings'			  => array(),
                                'title'               => '',
                                'aligndata'           => '',
                                'format'               => array(),  
                                'export_name' 		=> 'export_excel', 
                                'file_name' 		=> 'Inventory_',                           
                                'hiddenparam' 		=> '',                           
                                'db'               => null,   
								'exit' 		=> 'Y', 
								'warning' 		=> ''								
                        );
                        
                        if (func_num_args() == 1) {
                                $new_attribs = func_get_arg (0);
                                if (!$this->is_assoc ($new_attribs)) {
                                        die ("<BLINK>Pager expects first parameter to be associative ".
                                                "array.</BLINK>"
                                        );
                                };

                                
                                $this->attr = array_merge ($this->attr, $new_attribs);
                        };                        

                }

                function display_icon ($ExportURL = NULL) {
                        $sqlstring = base64_encode($this->attr['exportsql']);
                        $exportheadings = serialize($this->attr['headings']);
                        $exportformat = serialize($this->attr['format']);
                        $export_name = $this->attr['export_name'];
                        $title = $this->attr['title'];
                        $file_name = $this->attr['file_name'];
                        $hiddenparam = $this->attr['hiddenparam'];
						$warning = $this->attr['warning'];
						$onsubmit="";
						if (isset($warning) && $warning=="Y") {
							$onsubmit="onsubmit='return warning_msg();'";
						}
                        $action = '';
						if ($ExportURL)
							$action = 'action="'.$ExportURL.'"';
						
	                	$IconHTML=<<<EOD
						<script language='javascript'>
						function warning_msg() {
							var status=false;
							if (confirm("Data exported to Excel is limited to 2000 records, continue export?")){
								status=true;
							}
							return status;
						}
						</script>
                        <form name='excelform' method='POST' $action target='_blank' $onsubmit>
                        <input type=image alt='Export to Excel' name='$export_name' border=0 src='/images/icons/IconExcel.gif'>
                        <input type=hidden name='exportsql' value='$sqlstring'>
                        <input type=hidden name='exportheadings' value='$exportheadings'>
                        <input type=hidden name='exportformat' value='$exportformat'>
                        <input type=hidden name="title" value="$title">
                        <input type=hidden name='file_name' value='$file_name'>
                        $hiddenparam
                        </form>
EOD;
					return $IconHTML;
                }
 
                function format_data_cell ($colnum, $row) {

						$stylecode = strtoupper(substr ($this->attr['aligndata'], ($colnum-1), 1)); $style = "";
						if ($stylecode != '') {
								if ($stylecode == 'L') {$style = "style='text-align:left'";};
								if ($stylecode == 'C') {$style = "style='text-align:center'";};
								if ($stylecode == 'R') {$style = "style='text-align:right'";};
						};
				
                        if ($this->attr['format'][$colnum-1] != '') {

                                $data = $this->attr['format'][$colnum-1];


                                if (preg_match ("/{(\w+)}/", $data, $matches)) {
                                        // naughty ...
                                        $data = $matches[1] ($colnum, $row, true);
                                };


                                for ($ctr = 0; $ctr < count ($row); $ctr++) {
                                        $data = preg_replace (
                                                '/%'.$ctr.'%/',
                                                $row[$ctr],
                                                $data
                                        );
                                };

                        } else {
                                $data = $row[$colnum];
                        };

                      //  if ($data == '') {
                      //          $data = '&nbsp';
                      //  };

                        # replace "\n" with <BR>
                    //    $data = preg_replace('/\n/', '<BR>', $data);
                    $data = preg_replace('/\n/', ' ', $data);
                    $data = preg_replace('<BR>', ' ', $data);
//                        return "<TD ".$style.">$data</TD>";
                    return '"'.$data.'"'.",";
                }
                                
                function render () {
	
	                $exportheadings = $this->attr['headings'];
					$hiddenparam = $this->attr['hiddenparam'];
	                $db = $this->attr['db'];
	                if (is_null($this->attr['data']))
	                {
		                $db->setFetchMode(Zend_Db::FETCH_NUM);	
						$DataAll = $db->fetchAll($this->attr['exportsql']);	
	                }
	                else
	                {
	                	$DataAll = $this->attr['data'];
                	}
	                
			//		$content = "  <center>  <B> ".$this->attr['title']." </center> <BR>" ;
			
			//		$content .= "<table border='1' width='690' cellpadding='2' cellspacing='0' align='center'>
			//			<tr align='left' bgcolor=#cccccc>";$counter = 0;
					foreach ($exportheadings as $headerstr)
					{
						$stylecode = strtoupper(substr ($this->attr['aligndata'], $counter, 1)); $counter++; $style = "";
						if ($stylecode != '') {
								if ($stylecode == 'L') {$style = "style='text-align:left'";};
								if ($stylecode == 'C') {$style = "style='text-align:center'";};
								if ($stylecode == 'R') {$style = "style='text-align:right'";};
						};
					
			//			$content .= "<td ".$style."> &nbsp;<strong>".$headerstr."</strong> </td>";
                        $content .= $headerstr.",";
                    }
					$content .= "\n";
					foreach ($DataAll as $row)
					{
			//			$content .= "<tr>";
						
						$colnum = 0;
	                 	$maxcols = sizeof($this->attr['headings']);
	                    for ($col = 1; $col <= $maxcols; $col++) {
	                           $content .= $this->format_data_cell ($col, $row);
	                    };	

 						$content .= "\n";
						
					}	 
					if ($hiddenparam)
						$content .= $hiddenparam;
					
					
//					$content .= "</table>";
					
					
					if ($this->attr['exit']=="Y") {
//						header("Content-Type: application/vnd.ms-excel; name='excel'");
                        header("Content-Type: text/csv; name='excel'");
						header("Content-disposition:  attachment; filename=".$this->attr['file_name']."_".date("Y-m-d").".csv");
						echo $content;
						exit();
					} else {
						echo $content;
					}
                }  
                
                             

                function is_assoc($var) {
                        return is_array($var) && array_keys($var)!==range(0,sizeof($var)-1);
                }                

        }

?>