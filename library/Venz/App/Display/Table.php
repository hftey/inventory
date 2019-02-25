<?php

    
    class Venz_App_Display_Table
    {
        var $attr;
        var $sth;
        var $rowcount;
        var $colcount;
        var $row_index;
        var $data_counter;
        
        var $ajax_mode = false;
        var $ajax_refID = "";
        
        function Venz_App_Display_Table()
        {
            $this->attr = array(
                            'title'         => 'unnamed query',
                            'formname'      => 'aform',
                            'formaction'    => '',
                            'formparam'     => '',
                            'formexist'     => true,
                            'data'          => array(),
                            'values'        => array (),
                            'class'         => 'queryresults',
                            'norowsmsg'     => '&nbsp;No records found.',
                            'numcols'       => 0,
                            'pagelen'       => 5,
                            'alllen'        => 0,
                            'prev_button'   => '<',
                            'next_button'   => '>',
                            'prefix'        => 'Pager',
                            'page'          => 'Page ',
                            'of'            => 'of ',
                            'records'       => 'records',
                            'total'         => 'total:',
                            'sort_column'   => array(),
                            'sortby'        => '',
                            'ascdesc'       => '',
                            'sortby_name'   => 'sortby',
                            'ascdesc_name'  => 'ascdesc',
                            'tablewidth'    => '98%',
                            'tablepadding'  => '3',
                            'tablespacing'  => '0',
                            'tablealign'    => '',
                            'colparam'      => array(),
                            'hiddenparam'   => '',
                            'hiddenparamtop'=> '',
                            'export_excel'  => '',
                            'export_pdf'    => '',
                            'paging'        => true,
                            'currentPage'   => 0,
                            'footer_note'   => '',
                            'ajaxMode'      => false
                        );

            # if passed an assoc array as first parameter, merge the entries
            # there into $attr.
            if (func_num_args() == 1){
                $new_attribs = func_get_arg (0);
                if (!$this->is_assoc ($new_attribs)) {
                    die ("<BLINK>Pager expects first parameter to be associative ".
                            "array.</BLINK>"
                    );
                };
                
                $this->attr = array_merge ($this->attr, $new_attribs);
            };
            
            $this->data_counter = 0;
            
            $pagefield = $this->attr['prefix']."pagenum";
            if(isset($this->attr['currentPage']) && !empty($this->attr['currentPage'])){
                $pagefield = $this->attr['prefix']."pagenum";
                $_REQUEST[$pagefield] = $this->attr['currentPage'];
                
                $_REQUEST[$this->attr['prefix']. '_prev_page'] = $this->attr['currentPage']--;
                if($_REQUEST[$this->attr['prefix']. '_prev_page'] < 0){
                    $_REQUEST[$this->attr['prefix']. '_prev_page'] = "";
                }
                
                $_REQUEST[$this->attr['prefix']. '_next_page'] = $this->attr['currentPage']++;
            }
        }

        # returns an array containing the column headings.
        function get_headings()
        {
            return $this->attr['headings'];
        }

        # Figures out how many rows there are in our total selection, and
        # stores it in $this->numrows.
        function setup_get_count()
        {
            if ($this->attr['alllen']){
                $this->rowcount = $this->attr['alllen'];
            }
            else{
                $this->rowcount = sizeof($this->attr['data']);
            }
        }
        
        # this sets is up to query the data source (based on $attr['sql']. It
        # should leave a cursor in $this->sth, and also the total number of
        # rows in $this->rowcount.
        # By convention, the zeroth array element (first column in the select)
        # is expected to be the primary key.
        function setup_get_data()
        {
            # determine where in the series our page falls
            $limit = $this->attr['pagelen'];
            $pagefield = $this->attr['prefix']."pagenum";
            $offset = ($_REQUEST[$pagefield]-1) * $limit;
            
            $sql = $this->attr['sql']." LIMIT ".$limit." OFFSET ".$offset;
            $hquery = mysql_query($sql);
            
            $this->colcount =  mysql_num_rows($hquery);
            $this->results = $hquery;
        }
        
        # this fetches one row of data, and returns an array.
        function get_data()
        {
            $TempData = $this->attr['data'];
            if ($this->data_counter < sizeof($TempData) && $this->data_counter < $this->attr['pagelen']){
                $data = $TempData[$this->data_counter];
            }
            else{
	            $data = false;
            }
            
            $this->data_counter++;
            return $data;
        }
        
        function format_headings($row)
        {
            $r = "  <TR id='report_heading'>\n    ";
            
            foreach ($row as $index => $cell){
                $imgsrc = "";
                $field = "";
                if (isset($this->attr['sort_column'][$index])){
                    $field = $this->attr['sort_column'][$index];
                }
                $ascdesc = $this->attr['ascdesc'];
                if ($this->attr['sortby'] == $field){
                    $ascdesc = $this->attr['ascdesc'] == "asc" ? "desc" : "asc";
                    if ($ascdesc == "asc"){
                        $imgsrc = "&nbsp;<img border=0 src='/images/table/Arrowdown.gif'>";
                    }
                    else{
                        $imgsrc = "&nbsp;<img border=0 src='/images/table/Arrowup.gif'>";
                    }
                }
                else{
                    $ascdesc  = "asc";
                }
                
                $stylecode = strtoupper(substr ($this->attr['aligndata'], $index, 1));
                // $bgcolor
                if ($stylecode != '') {
                        if ($stylecode == 'L') {$style = "style='text-align:left'";};
                        if ($stylecode == 'C') {$style = "style='text-align:center'";};
                        if ($stylecode == 'R') {$style = "style='text-align:right'";};
                };
                
                $cell=trim($cell);
                if (strlen($cell) == 0){
                    $cell = "&nbsp;";
                }
                
                if (!empty($field)){
                    $r .=   "<TD  nowrap class='report_header' $style>".
                            "<a href='javascript:void(0)' class=link_header ".
                            "onclick='document.".$this->attr['formname'].".".$this->attr['sortby_name'].".value=\"$field\";document.".$this->attr['formname'].".".$this->attr['ascdesc_name'].".value=\"$ascdesc\";".
                            (
                                ($this->ajax_mode) 
                                ? "ajaxPager_submit( document.".$this->attr['formname'].", \"".$this->ajax_refID."\", document.".$this->attr['formname'].".action );"
                                : "document.".$this->attr['formname'].".submit();"
                                /*
                                ? "onclick=\"return ajaxPager_sortColumn( document.".$this->attr['formname'].", '".$this->attr['sortby_name']."', '".$this->attr['ascdesc_name']."', '".$field."', '".$ascdesc."' );\" "
                                : "onclick='document.".$this->attr['formname'].".".$this->attr['sortby_name'].".value=\"$field\";document.".$this->attr['formname'].".".$this->attr['ascdesc_name'].".value=\"$ascdesc\";document.".$this->attr['formname'].".submit();'"
                            */
                           ).
                            "'>".$cell."$imgsrc</a></TD>";
                }
                else{
                    $r .= "<TD  nowrap class='report_header' $style>".$cell."</TD>";
                }
            };
            $r .= "\n  </TR>\n";
            return $r;
        }
        
        # returns the HTML for a single cell. Empty cells are padded with
        # space, and a "\n" (carriage return) in the cell is replaced with
        # a <BR>.
        # This can be overridden to allow custom output for specific columns.
        function format_data_cell ($colnum, $row)
        {
            # determine cell style
            $stylecode = strtoupper(substr ($this->attr['aligndata'], $colnum-1, 1));
            if (sizeof($this->attr['colparam']) && isset($this->attr['colparam'][$colnum-1])){
                $param = $this->attr['colparam'][$colnum-1];
            }
            else{
                $param = "";
            }
            
            // $bgcolor
            if ($stylecode != '') {
                    if ($stylecode == 'L') {$style = "style='text-align:left'";};
                    if ($stylecode == 'C') {$style = "style='text-align:center'";};
                    if ($stylecode == 'R') {$style = "style='text-align:right'";};
            };
            
            # see if there is a format for this
            if ($this->attr['format'][$colnum-1] != '') {
                # The cell contents is built from the format string, replacing
                $data = $this->attr['format'][$colnum-1];
                
                # if the format string is {name}, then we don't take it
                # literally, but call the function name ($colnum, $row)
                # to return the actual format string.
                if (preg_match ("/{(\w+)}/", $data, $matches)) {
                    // naughty ...
                    $data = $matches[1] ($colnum, $row);
                };
                
                # The cell contents is built from the format string, replacing
                # "%nnn%" with the data from the record's column nnn.
                for ($ctr = 0; $ctr < count ($row); $ctr++) {
                    $data = preg_replace ('/%'.$ctr.'%/', $row[$ctr], $data);
                };
            }
            else{
                # if no format, take the "raw" column data
                $data = $row[$colnum];
            };
            
            # see if there is a "map" for this
            //       if (is_array ($this->attr['map'][$colnum-1])) {
            //               $data = $this->attr['map'][$colnum-1][$data];
            //       };

            # emit blank if empty
            if ($data == '') {
                $data = '&nbsp';
            };
            
            # replace "\n" with <BR>
            $data = preg_replace('/\n/', '<BR>', $data);
            
            # return it
            return "<TD class='report_cell' $param $style>$data</TD>";
        }
        
        # we skip the zeroth element of $row, assuming it to be the primary
        # key. $attr[numcols], if defined, holds the total number of columns
        # to be rendered. Otherwise, the number of columns depends on your
        # SELECT statement. We call format_data_cell for each column.
        function format_data_row ($row)
        {
            $r = "";
            
            $maxcols = $this->attr['numcols'];
            for ($col = 1; $col <= $maxcols; $col++){
                $r .= $this->format_data_cell ($col, $row);
            };
            
            return $r;
        }
		
		function format_footer_rowempty()
		{  

            $maxcols = ($this->colcount > $this->attr['numcols']) ? $this->colcount : $this->attr['numcols'];		
				$r = "<TR>\n".
                 "    <TD class='report_footer' colspan='$maxcols' align=center>&nbsp;</TD>\n".
                 "  </TR>\n";
            
            return $r;
		}
        
        # Generates the footer row. This contains buttons to go to the
        # previous and next page, and also a "jump to any page" textfield.
        # The total number of pages is also displayed.
        function format_footer_row ()
        {
            $maxcols = ($this->colcount > $this->attr['numcols']) ? $this->colcount : $this->attr['numcols'];
            $pagefield = $this->attr['prefix']."pagenum";
            
            $status = (!isset($_REQUEST[$pagefield]) || $_REQUEST[$pagefield] < 2) ?  'DISABLED ' : '';
            
            $ajaxAction = "";
            if($this->ajax_mode){
                $ajaxAction = " onClick=\"return ajaxPager_prevnext(this, '".$this->ajax_refID."'); \" ";
            }
            
            $prev_page = "<INPUT TYPE='submit' class='button_navi' NAME='". $this->attr['prefix']. "_prev_page' ".
                                "VALUE='". $this->attr['prev_button']."' {$ajaxAction} {$status}>";
            
            $maxpages = round(($this->rowcount / $this->attr['pagelen'])+.49);
            
            if ($maxpages == 0) { $maxpages = 1; };
            
            $status = (isset($_REQUEST[$pagefield]) && $_REQUEST[$pagefield] >= $maxpages) ?  'DISABLED ' : '';
            
            $prefixpagenum = '';
            if (isset($_REQUEST[$this->attr['prefix']."pagenum"])){
                $prefixpagenum = $_REQUEST[$this->attr['prefix']."pagenum"];
            }
            else{
                $prefixpagenum = "1";
            }
            
            
            $next_page = "<INPUT TYPE='submit' class='button_navi' NAME='". $this->attr['prefix']. "_next_page' ".
                                "VALUE='". $this->attr['next_button']."' {$ajaxAction} {$status}>";
            
            $page_drop_js = "<script language='Javascript'>\n";	
            $page_drop_js .= "function pager_onchangepage(obj) {";
            
            if($this->ajax_mode){
                $page_drop_js .= "ajaxPager_submit(obj.form, '".$this->ajax_refID."', '".$this->attr['formaction']."');";
            }
            else{
                $page_drop_js .= "obj.form.submit();";
            }
            
            $page_drop_js .= "}";	
            $page_drop_js .= "</script>";
            
            $page_drop = "<select id='".$this->attr['prefix']."pagenum' name='".$this->attr['prefix']."pagenum' onchange='pager_onchangepage(this)'>";	
            for ($i = 1; $i <= $maxpages; $i++){
                if ($prefixpagenum == $i){
								$page_drop .= "<option value='$i' selected>$i</option>";
                }
                else{
                    $page_drop .= "<option value='$i'>$i</option>";
                }
            }
            $page_drop .= "</select>";
            
            $r = "  {$page_drop_js}<TR>\n".
                 "    <TD class='report_footer' colspan='$maxcols' align=center>".
                        $prev_page .
                        "&nbsp;".
                        "<B>".$this->attr['page']."</B>".
                        $page_drop.
                        "<B>&nbsp;".$this->attr['of'].
                        '&nbsp;'.
                        $maxpages .
                        '&nbsp;('.
                        $this->attr['total'].
                        '&nbsp;'.
                        $this->rowcount.
                        '&nbsp;'.
                        $this->attr['records'].
                        ')&nbsp;'."</B>".
                        $next_page.
                 "    </TD>\n".
                 "  </TR>\n";
            
            return $r;
        }
        
        # Determine the variable is an array and not empty
        function is_assoc($var)
        {
            return is_array($var) && array_keys($var)!==range(0,sizeof($var)-1);
        }
        
        # Outputs HTML for the entire pager.
        function render ()
        {
            global $UNIVERSAL_COUNTER;
            $r = "";
            
            # current render mode
            $this->ajax_mode = (isset($this->attr['ajaxMode']) && $this->attr['ajaxMode']?true:false);
            
            # go to first page by default
            $pagefield = $this->attr['prefix']."pagenum";
            if (isset($_REQUEST[$pagefield])){
                if ($_REQUEST[$pagefield] == '' || $_REQUEST[$pagefield] == 0){
                    $_REQUEST[$pagefield] = 1;
                }
            }
            
            # process prev_page button
            if (isset($_REQUEST[$this->attr['prefix']. '_prev_page']) && isset($_REQUEST[$pagefield])){
                if ($_REQUEST[$this->attr['prefix']. '_prev_page'] != '' && $_REQUEST[$pagefield] >= 2){
                    $_REQUEST[$pagefield]--;
                }
            }
            
            $this->setup_get_count ();
            
            $maxpages = round(($this->rowcount / $this->attr['pagelen'])+.49);
            if ($maxpages == 0) { $maxpages = 1; }
            
            # process next_page button
            if (isset($_REQUEST[$this->attr['prefix']. '_next_page']) && isset($_REQUEST[$pagefield])){
                if ($_REQUEST[$this->attr['prefix']. '_next_page'] != '' && $_REQUEST[$pagefield] < $maxpages){
                    $_REQUEST[$pagefield]++;
                }
            }
            
            # go to last page if over the end
            if (isset($_REQUEST[$pagefield])){
                if ($_REQUEST[$pagefield] > $maxpages) {
                    $_REQUEST[$pagefield] = $maxpages;
                }
            }
            
            $formAction = "";
            if ($this->attr['formaction']){
                $formAction = "action='".$this->attr['formaction']."'";
            }
            
            # retrieve data (LIMIT and OFSET are added automatically)
            //$this->setup_get_data ();
            $this->attr['tablewidth'] = strlen($this->attr['tablewidth']) > 0 ? "width='".$this->attr['tablewidth']."'" : "";
            
            $strAlign = "";
            if ($this->attr['tablealign']){
                $strAlign = "align='".$this->attr['tablealign']."'";
            }
            
            if($this->ajax_mode){
                //var_dump($_REQUEST);
                if(isset($_REQUEST["ajaxPagerRefID"]) && !empty($_REQUEST["ajaxPagerRefID"])){
                    $this->ajax_refID = $_REQUEST["ajaxPagerRefID"];
                }
                else{
                    $this->ajax_refID = mt_rand()."_".time()."_".$this->attr['prefix'];
                    
                    $r .= "<div id='loaderDiv_".$this->ajax_refID."' style='display:none;'><img src='/images/loader/ajax-loader.gif' alt='' border=0 align='absmiddle' />&nbsp;<b>Page Loading...</b></div>";
                    $r .= "<div id='tblDiv_".$this->ajax_refID."'>";
                }
                
                //$formAction .= " onSubmit=\"ajaxPager_submit( this, '".$this->ajax_refID."', '".$this->attr['formaction']."'); return false;\" ";
                $formAction .= " onSubmit=\"ajaxPager_submit( this, '".$this->ajax_refID."', $(this).attr('action') ); return false;\" ";
            }
            $r .= "<br><TABLE ".$strAlign." cellspacing=".$this->attr['tablespacing']." cellpadding=".$this->attr['tablepadding']." ".$this->attr['tablewidth'].">\n";
            
            if (strlen($this->attr['title']) > 0 || strlen($this->attr['export_excel']) > 0) {
                $maxcols = $this->attr['numcols'];
                if ($this->rowcount > 0){
                    $r .=   " <TR>\n    ".
                            "  <TD class='report_title_top' align=left colspan='".
                                $maxcols . "'><table width=100% border=0 cellspacing=0 cellpadding=0><TR><TD  class='report_title' valign=middle>" . $this->attr['title'] . "</TD><TD valign=middle align=right width=20px>".$this->attr['export_excel']."</TD><TD valign=middle align=right width=20px>".$this->attr['export_pdf']."</TD></TR></TABLE></TD>\n".
                            " </TR>\n";
                }
                else{
                    $r .=   " <TR>\n    ".
                            "  <TD class='report_title_top' align=left colspan='".
                                $maxcols . "'><table width=100% border=0 cellspacing=0 cellpadding=0><TR><TD  class='report_title' valign=middle>" . $this->attr['title'] . "</TD></TR></TABLE></TD>\n".
                            " </TR>\n";
                }
            };
            
            if ($this->attr['formexist']){
                $r .=   "\n<!-- START OF PAGER " . $this->attr['prefix'] . " -->\n".
                        "\n<TR><TD align=left>".
                        "\n<form id='id".$this->attr['formname']."'  name='".$this->attr['formname']."' ".$formAction."  method=POST ".$this->attr['formparam']." style='margin: 0 0 0 0;'>" .
                        $this->attr['hiddenparamtop']. 
                        "<table border=0 class='report_table_wrapper' cellspacing=0 cellpadding=0 align=".$this->attr['tablealign']."><TR><TD><TABLE BORDER=0 align='".$this->attr['tablealign']."' class='report_table' cellspacing=".$this->attr['tablespacing']." cellpadding=".$this->attr['tablepadding']." width='100%'>\n";
            }
            else{
                $r .=   "\n<!-- START OF PAGER " . $this->attr['prefix'] . " -->\n".
                        "\n<TR><TD align=left>".
                        "\n".
                        $this->attr['hiddenparamtop'].
                        "<table border=0 class='report_table_wrapper' cellspacing=0 cellpadding=0  align=".$this->attr['tablealign']."><TR><TD><TABLE BORDER=0 align=".$this->attr['tablealign']." class='report_table' cellspacing=".$this->attr['tablespacing']." cellpadding=".$this->attr['tablepadding']." width='100%'>\n";
            }
            
            if ($this->rowcount > 0) {
                # emit heading row
                $headings = $this->get_headings();
                if (is_array ($headings) && count($headings) > 0) {
                    $r .= $this->format_headings ($headings);
                }
                
                # emit the data rows
                $rownum = 1;
                while ($row = $this->get_data ()) {
                    if ($rownum%2 != 0){
                        $r .=   "  <TR class='report_even'>\n    ".
                                    $this->format_data_row ($row).
                                "\n  </TR>\n";
                    }
                    else {
                        $r .=   "  <TR class='report_odd'>\n    ".
                                    $this->format_data_row ($row).
                                "\n  </TR>\n";
                    }
                    $rownum++;
                }
                
                if ($this->rowcount > $this->attr['pagelen'] && $this->attr['paging']){
                    $r .= $this->format_footer_row ();
                }
				
            }
            else {
                # emit message saying no rows available
                $r .=   "  <TR>\n    <TD class='report_error' align=left>" . $this->attr['norowsmsg'].
                        "<TD>\n  </TR>\n";
            }
            
            if ($this->attr['formexist']){
                $r .=   "</TABLE></TD></TR></TABLE>".$this->attr['footer_note']."<input type=hidden name=".$this->attr['sortby_name']." value='".$this->attr['sortby']."'>".
                        (($this->ajax_mode)?"<input type=hidden name='ajaxPagerRefID' value='".$this->ajax_refID."'><input type=hidden name='ajaxPagerPrefix' value='".$this->attr['prefix']."'>":"").
                        "<input type=hidden name=".$this->attr['ascdesc_name']." value='".$this->attr['ascdesc']."'>".$this->attr['hiddenparam'].
                        //"<input type=button onClick=\"ajaxPager_submit(this.form, '".$this->ajax_refID."', '".$this->attr['formaction']."');\" />".
                        " </form></TD></TR></TABLE>\n".
                        "<!-- END OF PAGER " . $this->attr['prefix'] . " -->\n";
            }
            else{
                $r .=   "</TABLE></TD></TR></TABLE>".$this->attr['footer_note']."<input type=hidden name=".$this->attr['sortby_name']." value='".$this->attr['sortby']."'>".
                        (($this->ajax_mode)?"<input type=hidden name='ajaxPagerRefID' value='".$this->ajax_refID."'><input type=hidden name='ajaxPagerPrefix' value='".$this->attr['prefix']."'>":"").
                        " <input type=hidden name=".$this->attr['ascdesc_name']." value='".$this->attr['ascdesc']."'>".$this->attr['hiddenparam']."</TD></TR></TABLE>\n".
                        "<!-- END OF PAGER " . $this->attr['prefix'] . " -->\n";
            }
            
            if($this->ajax_mode){
                if(!isset($_REQUEST["ajaxPagerRefID"]) || empty($_REQUEST["ajaxPagerRefID"])){
                    $r .= "</div>";
                }
            }
            
            return $r;
        }
        # end function render
        
    }
    