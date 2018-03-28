<?php 
$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/bin/CORAL/class/CoralDatabase.php";
include $path;
function print_subject($sid, $cid, $url, $tut, $graphic, $desc, $proxy = "", $openStyle = "") {
    
    $coralDB = new CoralDatabase();

    $outString = "";
    $newUrl = "&nbsp;<img src=\"http://library.olivet.edu/pix/new.gif\" title=\"New\" />";

    $categoryString = "(";
    foreach ($cid as &$id)
        $categoryString .= "r.resourceTypeID = '$id' OR ";
    $categoryString = rtrim($categoryString, " OR ").")";

    $urlType = ($url == 'adv') ?
                   "resourceAltURL" :
                   "resourceURL";
    $showGraphic = ($graphic == 'gon');
    $float = ($desc == 'flo');

    if ($tut != "not")
        $tutorialExist = true;
    else
        $tutorialExist = false;

    $resourceString = sprintf("SELECT r.titleText rname,
                                r.$urlType rurl,
                                r.descriptionText,
                                r.updateDate,
                                r.statusID,
                                r.acquisitionTypeID,
                                o.name oname,
                                (SELECT noteText FROM bennerlib_coral_resources.ResourceNote WHERE noteTypeID = 8 AND resourceID = r.resourceID) as tut,
                                (SELECT noteText FROM bennerlib_coral_resources.ResourceNote WHERE noteTypeID = 12 AND resourceID = r.resourceID) as ezproxy,
                                (SELECT noteText FROM bennerlib_coral_resources.ResourceNote WHERE noteTypeID = 7 AND resourceID = r.resourceID) as db_icon_id
                                FROM bennerlib_coral_resources.Resource r, bennerlib_coral_organizations.Organization o, bennerlib_coral_resources.ResourceOrganizationLink x1
                                WHERE r.resourceID IN (SELECT resourceID
                                                        FROM bennerlib_coral_resources.ResourceSubject
                                                        WHERE generalDetailSubjectLinkID = '%s') AND
                                r.resourceID = x1.resourceID AND
                                o.organizationID = x1.organizationID AND
                                archiveDate IS NULL AND
                                statusID = 1 AND
                                $categoryString
                                ORDER BY r.titleText",
                        mysqli_real_escape_string($coralDB->conn, $sid));
    $resourceQuery = mysqli_query($coralDB->conn, $resourceString) or die(mysqli_error($coralDB->conn));

    while ($result = mysqli_fetch_array($resourceQuery)) {
        $result['trial'] = FALSE;

        if ($result['ezproxy'] == 'yes') {
            $proxyUrl = ($proxy == "") ? "https://login.proxy.olivet.edu/login?url=" : "";
        }
        
        if (date("Y-m-d", strtotime(date("Y-m-d", strtotime($result['updateDate'])) . " +6 month")) >=
                date("Y-m-d"))
            $newpic = $newUrl;
        else
            $newpic = '';
        if ($openStyle == '' && $proxy != 'window' && $proxy != 'none')
          $styleString = 'target=\"_blank\"';
        else if ($openStyle == 'window' || $proxy == 'window')
          $styleString = "onClick=\"popup = window.open('$proxyUrl$result[rurl]', 'PopupPage', 'height=500,width=500,scrollbars=yes,resizable=yes');
                                return false;\"
                      target=\"_blank\"";
        else if ($openStyle == 'none' || $proxy == 'none')
          $styleString = '';
          
        $graphicClass = " class='";
        $graphicString = "(";
        $graphicTitle = "";
        
        if ($showGraphic)
        {
            switch($result['db_icon_id'])
            {
                case "All fulltext":
                    $graphicClass .= "full-text";
                    $graphicString .= "all full text)";
                    $graphicTitle .= " title='All Full Text'";
                    break;
                case "Some fulltext":
                    $graphicClass .= "some-full-text";
                    $graphicString .= "some full text)";
                    $graphicTitle .= " title='Some Full Text'";
                    break;
                case "Citation/abstract only":
                    $graphicClass .= "no-full-text";
                    $graphicString .= "no full text)";
                    $graphicTitle .= " title='Citation/Abstract Only'";
                    break;
                case "Multimedia":
                    $graphicClass .= "multimedia";
                    $graphicString .= "multimedia)";
                    $graphicTitle .= " title='Multimedia Database'";
                    break;
            }
        } else {
            $graphicClass .= "no-graphic";
            $graphicStiring = "";
        }

        if (strlen($newpic) > 0) {
            $graphicClass .= " new'";
        } else {
            $graphicClass .= "'";
        }
        
        // $outString .= "<li ".$graphicClass.">";
        // $outString .= "<a href='".$proxyUrl.$result['rurl']."' ".$styleString."><span class=\"db-type-icon\"".$graphicTitle."></span>".$result['rname'];
        // if ($float) {
        //     $outString .= "<span class=\"more-info\">";
        //     if ($tutorialExist && $result['tut'] != "") {
        //         $outString .= "&nbsp;<a class='tut-link' href='".$result['tut']."'></a>";
        //     }
        //     $outString .= "</span></a><div><p>Service = ".$result['sname']."</p><p>".$result['description']."</p></div>";
        // } else {
        //     $outString .= "</a>";
        //     $outString .= "<div></div>(".$result['sname'].")<p>".$result['description']."</p>";
        // }

        // $outString .= "</li>";
        
        if ($result['trial']){
            $trial = " <strong><span style='color:green;'>Trial Database (T)</span></strong>";
        } else {
            $trial = "";
        }

        $outString .=  "<li ".$graphicClass."\">
                            <a href=\"".$proxyUrl.$result['rurl']." \" ".$styleString." title=\"".$result['rname']."\">
                                <span class=\"db-type-icon\"".$graphicTitle."></span>
                                ".$result['rname']." ".$trial."
                                <span class=\"more-info\"></span>
                            </a>
                            <div>
                                <p>Service = ".$result['oname']."</p>
                                <p>".$result['descriptionText']." ".$graphicString;


                                if ($tutorialExist && $result['tut'] != "") {
                                    $outString .= "<a class=\"tut-link\" title=\"Tutorial\" target='_blank' href=\"".$result['tut']."\"></a>";
                                }


        $outString .=  "    </div>
                        </li>";
    }
    return $outString;
}
?>