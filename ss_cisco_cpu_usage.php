<?php

/* do NOT run this script through a web browser */
if (!isset($_SERVER['argv'][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
        die('<br><strong>This script is only meant to run at the command line.</strong>');
}

global $config;

$no_http_headers = true;

/* display No errors */
error_reporting(0);

if (!isset($called_by_script_server)) {
        include_once(dirname(__FILE__) . '/../include/global.php');
        include_once(dirname(__FILE__) . '/../lib/snmp.php');

        array_shift($_SERVER['argv']);

        print call_user_func_array('ss_cisco_cpu_usage', $_SERVER['argv']);
} else {
        include_once($config['library_path'] . '/snmp.php');
}

function ss_cisco_cpu_usage($hostname, $host_id, $snmp_auth, $cmd, $arg1 = '', $arg2 = '')
{

# |host_hostname| |host_id| |host_snmp_version|:|host_snmp_port|:|host_snmp_timeout|:
# |host_ping_retries|:|host_max_oids|:|host_snmp_community|:|host_snmp_username|:
# |host_snmp_password|:|host_snmp_auth_protocol|:|host_snmp_priv_passphrase|:
# |host_snmp_priv_protocol|:|host_snmp_context|

    $snmp = explode(':', $snmp_auth);
    $snmp_version   = $snmp[0];
    $snmp_port      = $snmp[1];
    $snmp_timeout   = $snmp[2];
    $ping_retries   = $snmp[3];
    $max_oids       = $snmp[4];

    $snmp_auth_username     = '';
    $snmp_auth_password     = '';
    $snmp_auth_protocol     = '';
    $snmp_priv_passphrase   = '';
    $snmp_priv_protocol     = '';
    $snmp_context           = '';
    $snmp_community         = '';

    if ($snmp_version == 3) {
        $snmp_auth_username   = $snmp[6];
        $snmp_auth_password   = $snmp[7];
        $snmp_auth_protocol   = $snmp[8];
        $snmp_priv_passphrase = $snmp[9];
        $snmp_priv_protocol   = $snmp[10];
        $snmp_context         = $snmp[11];
    } else {
        $snmp_community = $snmp[5];
    }

    $oids = array (
        "cpuIndex" => ".1.3.6.1.4.1.9.9.109.1.1.1.1.2",
        "cpuDesc" => ".1.3.6.1.2.1.47.1.1.1.1.7",
        "cpu1min" => ".1.3.6.1.4.1.9.9.109.1.1.1.1.7",
        "cpu5min" => ".1.3.6.1.4.1.9.9.109.1.1.1.1.8"
    );

    if (($cmd == 'index')) {
        $arr_index = ss_cisco_cpu_get_indexes($hostname, $snmp_community, $oids['cpuIndex'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids);

        foreach ($arr_index as $index => $value) {
            print $index . "\n";
        }
    } elseif (($cmd == 'num_indexes')) {
        $arr_index = ss_cisco_cpu_get_indexes($hostname, $snmp_community, $oids['cpuIndex'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids);
        return sizeof($arr_index);
    } elseif ($cmd == 'query') {
        switch ($arg1) {
            case "cpuDevice":
                $arr = ss_cisco_cpu_get_indexes($hostname, $snmp_community, $oids['cpuIndex'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids);
                break;

            case "cpuName":
                $arr_index = ss_cisco_cpu_get_indexes($hostname, $snmp_community, $oids['cpuIndex'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids);

                foreach ($arr_index as $index => $value) {
                    $arr[$index] = 'CPU' . $index;
                }
                break;

            case "cpuDesc":
                $arr = ss_cisco_cpu_get_desc($hostname, $snmp_community, $oids['cpuIndex'], $oids['cpuDesc'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids);
                break;

            case "oneMin":
                $arr = ss_cisco_cpu_get_usage($hostname, $snmp_community, $oids['cpu1min'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids);
                break;

            case "fiveMin":
                $arr = ss_cisco_cpu_get_usage($hostname, $snmp_community, $oids['cpu5min'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids);
                break;
        }

        foreach ($arr as $index => $value) {
            print $index . ':' . $value . "\n";
        }
    } elseif ($cmd == 'get') {
        switch ($arg1) {
            case "cpuDevice":
                $arr = ss_cisco_cpu_get_indexes($hostname, $snmp_community, $oids['cpuIndex'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids);
                break;

            case "cpuName":
                $arr_index = ss_cisco_cpu_get_indexes($hostname, $snmp_community, $oids['cpuIndex'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids);

                foreach ($arr_index as $index => $value) {
                    $arr[$index] = 'CPU' . $index;
                }
                break;

            case "cpuDesc":
                $arr = ss_cisco_cpu_get_desc($hostname, $snmp_community, $oids['cpuIndex'], $oids['cpuDesc'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids);
                break;

            case "oneMin":
                $arr = ss_cisco_cpu_get_usage($hostname, $snmp_community, $oids['cpu1min'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids);
                break;

            case "fiveMin":
                $arr = ss_cisco_cpu_get_usage($hostname, $snmp_community, $oids['cpu5min'], $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids);
                break;
        }
    
        $index = rtrim($arg2);

        if (isset($arr[$index])) {
            return $arr[$index];
        } else {
            cacti_log('ERROR: Invalid Return Value in ss_cisco_cpu_usage.php for get ' . $index . ' and host_id ' . $host_id, false);
            return 'U';
        }
    }
}

// "cpuIndex" => ".1.3.6.1.4.1.9.9.109.1.1.1.1.2",
// "cpuDesc" => ".1.3.6.1.2.1.47.1.1.1.1.7",
// "cpu1min" => ".1.3.6.1.4.1.9.9.109.1.1.1.1.7",
// "cpu5min" => ".1.3.6.1.4.1.9.9.109.1.1.1.1.8"

function ss_cisco_cpu_get_desc($hostname, $snmp_community, $indexoid, $descoid, $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids)
{
    $index_arr = ss_cisco_cpu_reindex(cacti_snmp_walk($hostname, $snmp_community, $indexoid, $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
    $hw_arr = ss_cisco_cpu_reindex(cacti_snmp_walk($hostname, $snmp_community, $descoid, $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));

    $return_arr = array();

    foreach ($index_arr as $index => $hw_index) {
        if (is_numeric($index)) {
            if (isset($hw_arr[$hw_index])) {
                $return_arr[$index] =  $hw_arr[$hw_index];
            } else {
                $return_arr[$index] = 'Unknown CPU ' . $hw_index;
            }
        }
    }

    return $return_arr;
}

function ss_cisco_cpu_get_usage($hostname, $snmp_community, $oid, $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids)
{
    return ss_cisco_cpu_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid, $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
}

function ss_cisco_cpu_get_indexes($hostname, $snmp_community, $oid, $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids)
{
    $arr = ss_cisco_cpu_reindex(cacti_snmp_walk($hostname, $snmp_community, $oid, $snmp_version, $snmp_auth_username, $snmp_auth_password, $snmp_auth_protocol, $snmp_priv_passphrase, $snmp_priv_protocol, $snmp_context, $snmp_port, $snmp_timeout, $ping_retries, $max_oids, SNMP_POLLER));
    return $arr;
}

function ss_cisco_cpu_reindex($arr)
{
    $return_arr = array();
    for ($i=0; ($i<sizeof($arr)); $i++) {
        $index = substr($arr[$i]['oid'], strrpos($arr[$i]['oid'], '.') + 1);
        if (is_numeric($index)) {
            $return_arr[$index] = $arr[$i]['value'];
        }
    }

    return $return_arr;
}
