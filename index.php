<?php
$share_url = "https://1drv.ms/f/s!AhzmQMxm1n467DZsz0ev8jKEqskE";
$path = $_GET['path'];

function get_encodedURL($share_url) {
    $base64Value = base64_encode($share_url);
    return "https://api.onedrive.com/v1.0/shares/u!" . str_replace("+","-",str_replace("/","_",rtrim($base64Value,"=")));
}

function get_folder_object_url($share_url) {
    return get_encodedURL($share_url) . "/driveItem?\$expand=children";
}

function get_folder_object($share_url) {
    $folder_object_url = get_folder_object_url($share_url);
    $response = file_get_contents($folder_object_url);
    return json_decode($response, true);
}

function get_item_from_path($folder_share_url, $path) {
    $result = Array();
    $res = get_folder_object($folder_share_url);
    if ($path == "/" || $path == "") {
        $result['pathvalid'] = true;
        $result['isfolder'] = true;
        $result['data'] = $res;
        return $result;
    }

    if (isset($res['children']) && count($res['children']) > 0) {
        $items = $res['children'];
        $nodes = explode('/',substr($path, 1), 2);
        $next = $nodes[0];
        $rest = count($nodes) > 1 ? $nodes[1] : "";

        foreach ($items as $item) {
            if ($item['name'] == $next) {
                if (isset($item['folder'])) {
                    return get_item_from_path($item['webUrl'], "/" . $rest);
                } else if (isset($item['file'])) {
                    $result['pathvalid'] = true;
                    $result['isfolder'] = false;
                    $result['data'] = $item;
                    return $result;
                }
            }
        }

    }

    $result['pathvalid'] = false;
    return $result;
    
}

$result = get_item_from_path($share_url, $path);

if ($result['pathvalid'] && $result['isfolder'] == false) {
    $download_url = $result['data']['@content.downloadUrl'];
    header("Location: $download_url", true, 302);
    exit();
}

?>

<!doctype html>
<html lang="en">
  <head>
    <title>Download</title>
  </head>

  <body id="index-page">
    <?php
        echo "<h1>" . ($result['pathvalid'] ? ($result['isfolder'] ? "Index of " . $path : ("the download link to file is: " . $result['data']['@content.downloadUrl'])) : "Path not valid") . "</h1>" . PHP_EOL;
        echo "<br>";
        if ($result['pathvalid'] && $result['isfolder']) {
            $data = $result['data'];
            echo "<hr>";
            if ($path != "/") {
                echo "<h3 style=\"height:28px;\">" . "<a style=\"text-decoration:none;display: inline-block;height:26px;\" href=\"http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . rtrim($_SERVER['HTTP_HOST'], "/") . implode('/',explode('/',rtrim($path,"/"),-1)) . "/" . "\">" . 
                "<img alt=\"\" src=\"data:image/gif;base64,R0lGODlhFAAWAMIAAP///8z//5mZmWZmZjMzMwAAAAAAAAAAACH+TlRoaXMgYXJ0IGlzIGluIHRoZSBwdWJsaWMgZG9tYWluLiBLZXZpbiBIdWdoZXMsIGtldmluaEBlaXQuY29tLCBTZXB0ZW1iZXIgMTk5NQAh+QQBAAABACwAAAAAFAAWAAADSxi63P4jEPJqEDNTu6LO3PVpnDdOFnaCkHQGBTcqRRxuWG0v+5LrNUZQ8QPqeMakkaZsFihOpyDajMCoOoJAGNVWkt7QVfzokc+LBAA7\" />" .
                " " . "<div style=\"display:inline-block;\">" . "Parent Directory" . "</div>" . "</a>" . "</h3>" . PHP_EOL;
            }
            foreach ($data['children'] as $item) {
                echo "<h3 style=\"height:28px;\">" . "<a style=\"text-decoration:none;display: inline-block;height:26px;\" href=\"http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . rtrim($_SERVER['HTTP_HOST'], "/") . ($path == "/" ? "" : rtrim($path,"/")) . "/" . $item['name'] . "\">" . 
                (isset($item['folder']) ? 
                "<img style=\"display:inline-block;vertical-align:bottom;\" height=\"24\" alt=\"d \" src=\"data:image/gif;base64,R0lGODlhFAAWAMIAAP/////Mmcz//5lmMzMzMwAAAAAAAAAAACH+TlRoaXMgYXJ0IGlzIGluIHRoZSBwdWJsaWMgZG9tYWluLiBLZXZpbiBIdWdoZXMsIGtldmluaEBlaXQuY29tLCBTZXB0ZW1iZXIgMTk5NQAh+QQBAAACACwAAAAAFAAWAAADVCi63P4wyklZufjOErrvRcR9ZKYpxUB6aokGQyzHKxyO9RoTV54PPJyPBewNSUXhcWc8soJOIjTaSVJhVphWxd3CeILUbDwmgMPmtHrNIyxM8Iw7AQA7\" />" 
                : 
                "<img style=\"display:inline-block;vertical-align:bottom;\" alt=\"f \" src=\"data:image/gif;base64,R0lGODlhFAAWAMIAAP///8z//5mZmTMzMwAAAAAAAAAAAAAAACH+TlRoaXMgYXJ0IGlzIGluIHRoZSBwdWJsaWMgZG9tYWluLiBLZXZpbiBIdWdoZXMsIGtldmluaEBlaXQuY29tLCBTZXB0ZW1iZXIgMTk5NQAh+QQBAAABACwAAAAAFAAWAAADWDi6vPEwDECrnSO+aTvPEddVIriN1wVxROtSxBDPJwq7bo23luALhJqt8gtKbrsXBSgcEo2spBLAPDp7UKT02bxWRdrp94rtbpdZMrrr/A5+8LhPFpHajQkAOw==\" />"
                ) . 
                " " . "<div style=\"display:inline-block;\">" . $item['name'] . "</div>" . "</a>" . "</h3>" . PHP_EOL;
            }
            echo "<hr>";
        }
    ?>
  </body>
</html>