<?php 
function print_resource($arg0, $arg1, $arg2 = "", $arg3 = "", $arg4 = "", $arg5 = "", $arg6 = "", $arg7 = "", $arg8 = "", $arg9 = "") {

    /**
    * If all of the arguments are in, they will be in the order below.
    *
    * Resource(s)
    * URL Type
    * Graphic
    * Description Type
    * Tutorial
    * Inside List
    * Open Style
    * EZProxy
    *
    * However, not all of these are always needed. In the future, we should probably always pass all parameters.
    */

    /**
    * Setup Database
    */
    if (!isset($coralDB)) {
        $coralDB = new CoralDatabase();
    }

    /**
    * Resource Name will be arg0 as long as a resource is selected
    */
    $resource_name = $arg0;
    $outString = "";

    /**
    * Get all information about resource - including notes
    */
    $linkQuery = ('SELECT *,
                    (SELECT noteText FROM bennerlib_coral_resources.ResourceNote WHERE noteTypeID = 8 AND resourceID = r.resourceID) as tut,
                    (SELECT noteText FROM bennerlib_coral_resources.ResourceNote WHERE noteTypeID = 12 AND resourceID = r.resourceID) as ezproxy,
                    (SELECT noteText FROM bennerlib_coral_resources.ResourceNote WHERE noteTypeID = 7 AND resourceID = r.resourceID) as db_icon_id
                   FROM bennerlib_coral_resources.Resource r, bennerlib_coral_resources.ResourceOrganizationLink l
                   WHERE r.resourceID = l.resourceID
                   AND r.resourceID IN ('.$resource_name.')
                   ORDER BY r.titleText');
    $query = mysqli_query($coralDB->conn,$linkQuery) or die(mysqli_error($coralDB->conn));
    $num_rows = mysqli_num_rows($query);
    while($results = mysqli_fetch_array($query)){

    $supplierQuery = ('SELECT name
                       FROM bennerlib_coral_organizations.Organization
                       WHERE organizationID = "'.$results['organizationID'].'"');
    $query3 = mysqli_query($coralDB->conn,$supplierQuery) or die(mysqli_error($coralDB->conn));
    $supplierResults = mysqli_fetch_array($query3);

    /**
    * Set the graphic and title text
    */
    $graphicString = " class='";
    $graphicTitle = "";
    if ($arg2 == "graphic") {
        switch($results['db_icon_id']) {
            case "All fulltext":
                $graphicString .= "full-text";
                $graphicTitle .= " title='All Full Text'";
                break;
            case "Some fulltext":
                $graphicString .= "some-full-text";
                $graphicTitle .= " title='Some Full Text'";
                break;
            case "Citation/abstract only":
                $graphicString .= "no-full-text";
                $graphicTitle .= " title='Citation/Abstract Only'";
                break;
            case "Multimedia":
                $graphicString .= "multimedia";
                $graphicTitle .= " title='Multimedia Database'";
                break;
        }
    }
    else {
        $graphicString .= "no-graphic";
    }

    // add infor for new graphic...
    $graphicString .= "'";


    // Set Proxy
    $ezproxy = "no_proxy";
    if ($arg2 == $ezproxy ||
        $arg3 == $ezproxy ||
        $arg4 == $ezproxy ||
        $arg5 == $ezproxy ||
        $arg6 == $ezproxy ||
        $arg7 == $ezproxy ||
        $arg8 == $ezproxy ||
        $arg9 == $ezproxy)
        $proxy = "";
    else {
        if ($results['ezproxy'] == 'yes') {
            $proxy = "https://login.proxy.olivet.edu/login?url=";
        }
    }

    // Set the URL
    if ($arg1 == "basic")
        $url = $proxy.$results['resourceURL'];
    else
        $url = $proxy.$results['resourceAltURL'];

    // Set URL opening style
    if ($arg2 == "window" ||
        $arg3 == "window" ||
        $arg4 == "window" ||
        $arg5 == "window" ||
        $arg6 == "window" ||
        $arg7 == "window")
        $openStyle = "onClick=\"popup = window.open('$url', 'PopupPage', 'height=500,width=500,scrollbars=yes,resizable=yes');
                                return false;\"
                      target=\"_blank\"";
    else if ($arg2 == "none" ||
             $arg3 == "none" ||
             $arg4 == "none" ||
             $arg5 == "none" ||
             $arg6 == "none" ||
             $arg7 == "none")
        $openStyle = "";
    else
        $openStyle = "target=\"_blank\"";
    $url = "<a href=\"$url\" $openStyle>";

    // Determine and set new graphic
    if (date("Y-m-d", strtotime(date("Y-m-d", strtotime($results['createDate'])) . " +6 month")) >=
            date("Y-m-d") && ($arg2 == "new_graphic" ||
                                $arg3 == "new_graphic" ||
                                $arg4 == "new_graphic" ||
                                $arg5 == "new_graphic" ||
                                $arg6 == "new_graphic" ||
                                $arg7 == "new_graphic" ||
                                $arg8 == "new_graphic")) 
        $newPic = "<img class=\"new-link\"></a>";
    else
        $newPic = '';

    if ($results['acquisitionTypeID'] == '3' && ($arg2 == "trial_graphic" ||
                                                    $arg3 == "trial_graphic" ||
                                                    $arg4 == "trial_graphic" ||
                                                    $arg5 == "trial_graphic" ||
                                                    $arg6 == "trial_graphic" ||
                                                    $arg7 == "trial_graphic" ||
                                                    $arg8 == "trial_graphic" ||
                                                    $arg9 == "trial_graphic")) {
        if ($newPic != '') {
            $trialGraphic = "<img class=\"trial-link\"></a>";
        } else {
            $trialGraphic = "<img class=\"trial-link\"></a>";
        }
    } else {
        $trialGraphic = '';
    }

 // Determine and set tutorial (either company or Benner Library associated
    $company = "tutorial";
    $bl = "tutorial";
    $tutorialpic = "";
    if ($arg2 == $company ||
        $arg3 == $company ||
        $arg4 == $company ||
        $arg5 == $company ||
        $arg6 == $company ||
        $arg7 == $company){
        if(!is_null($results['tut'])&&$results['tut']!="http://"&&$results['tut']!="")
        $tutorialpic = "<a  class=\"tut-link\" href=\"$results[tut]\"></a>";
        else $tutorialpic = "";
    }

    // Set description
    $float = "float_description";
    $print = "print_description";
    $none  = "no_description";
    if ($arg2 == $float ||
        $arg3 == $float ||
        $arg4 == $float ||
        $arg5 == $float ||
        $arg6 == $float ||
        $arg7 == $float)
        $description = "<span class='more-info'></span></a><div><p>Service = ".$supplierResults['name']."</p><p>".$results['descriptionText'].$tutorialpic."</p></div>".$newPic.$trialGraphic;
    else if ($arg2 == $print ||
             $arg3 == $print ||
             $arg4 == $print ||
             $arg5 == $print ||
             $arg6 == $print ||
             $arg7 == $print)
               $description = "</a><div><p>Service = ".$supplierResults['supplier_name']."</p><p>".$results['description'].$tutorialpic."</p></div>".$newPic."<br/>".$trialGraphic;
    else
        $description = "</a>";

    if ($arg2 == 'no-li' ||
        $arg3 == 'no-li' ||
        $arg4 == 'no-li' ||
        $arg5 == 'no-li' ||
        $arg6 == 'no-li' ||
        $arg7 == 'no-li')
    {
        $outString = $url.$results['titleText'];

        if ($arg2 == 'float_description' ||
        $arg3 == 'float_description' ||
        $arg4 == 'float_description' ||
        $arg5 == 'float_description' ||
        $arg6 == 'float_description' ||
        $arg7 == 'float_description') {
            $outString .= "<span class=\"float-description\">Service = ".$supplierResults['name']."<br>".$results['descriptionText']."</span>";
        }

        $outString .= '</a>';
    } else {
        $outString = "<li ".$graphicString.">";
        $outString .= $url."<span class=\"db-type-icon\"".$graphicTitle."></span>".$results['titleText'];
        $outString .= $description;
    }

    echo $outString;
    }
}
?>
