<?php 
function print_resource($arg0, $arg1, $arg2 = "", $arg3 = "", $arg4 = "", $arg5 = "", $arg6 = "", $arg7 = "") {

	if (!isset($coralDB)) {
        $coralDB = new CoralDatabase();
    }

    $resource_name = $arg0;
    $outString = "";
    $graphicString = ""; // fulltext, some, etc.
    $graphicTitle = ""; // text for hover
    $proxy = ""; // if needs proxy
    $url = ""; 
    $openStyle = ""; // new window, new tab, same
    $newpic = ""; // picture that shows that it was created within last 6 months
    $float = "float_description"; // description shows on hover
    $drop = "drop_description"; // description shows once dropdown arrow is clicked
    $under = "under_description"; // description is printer after on new line
    $after = "after_description"; // description is printed after on same line
    $none = "no_description"; // just link is shown
    $description = ""; 
    $tutorialpic = ""; // shows an image with link to tutorial

    // Get resource information
    $linkQuery = ('SELECT *,
                    (SELECT noteText FROM coral_resources.resourceNote WHERE noteTypeID = 8 AND resourceID = r.resourceID) as tut,
                    (SELECT noteText FROM coral_resources.resourceNote WHERE noteTypeID = 12 AND resourceID = r.resourceID) as ezproxy,
                    (SELECT noteText FROM coral_resources.resourceNote WHERE noteTypeID = 7 AND resourceID = r.resourceID) as db_icon_id
                   FROM resource r, resourceorganizationlink l
                   WHERE r.resourceID = l.resourceID
                   AND r.resourceID IN "'.$resource_name.'"');
    $query = mysqli_query($coralDB->conn,$linkQuery) or die(mysqli_error($coralDB->conn));
    $results = mysqli_fetch_array($query);
    $num_rows = mysqli_num_rows($query);

    // Get organization (previously known as supplier) information
    $supplierQuery = ('SELECT name
                       FROM coral_organizations.organization
                       WHERE organizationID = "'.$results['organizationID'].'"');
    $query3 = mysqli_query($coralDB->conn,$supplierQuery) or die(mysqli_error($coralDB->conn));
    $supplierResults = mysqli_fetch_array($query3);


    // If graphic, add it to outString
    if ($arg2 = "graphic") {
    	$outString .= "<img src='/img/css/";
    	switch($results['db_icon_id']) {
    		case "All fulltext":
    			$outString .= "full-text.png' title='All Full Text'>";
    			break;
    		case "Some fulltext":
    			$outString .= "some-full-text.png' title='Some Full Text'>";
    			break;
    		case "Citation/abstract only":
    			$outString .= "no-full-text.png' title='Citation/Abstract Only'>";
    			break;
    		case "Multimedia":
    			$outString .= "Multimedia.png' title='Multimedia Database'>";
    			break;
    	}
    }