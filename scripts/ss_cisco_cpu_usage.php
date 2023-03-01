#!/usr/bin/env php
<?php

/* display No errors */
error_reporting(1);

if (!isset($called_by_script_server)) {
    include_once dirname(__FILE__).'/../include/cli_check.php';
    include_once dirname(__FILE__).'/../lib/snmp.php';

    array_shift($_SERVER['argv']);

    echo call_user_func_array('ss_cisco_cpu_usage', $_SERVER['argv']);
} else {
    include_once dirname(__FILE__).'/../lib/snmp.php';
}

function ss_cisco_cpu_usage($hostname = '', $host_id = 0, $snmp_auth = '', $cmd = 'index', $arg1 = '', $arg2 = '')
{
    // |host_hostname| |host_id| |host_snmp_version|:|host_snmp_port|:|host_snmp_timeout|:
    // |host_ping_retries|:|host_max_oids|:|host_snmp_community|:|host_snmp_username|:
    // |host_snmp_password|:|host_snmp_auth_protocol|:|host_snmp_priv_passphrase|:
    // |host_snmp_priv_protocol|:|host_snmp_context|:|host_snmp_engine_id|:

    $snmp = explode(':', $snmp_auth);
    $host_args = [];

    $host_args['version'] = $snmp[0];
    $host_args['port'] = $snmp[1];
    $host_args['timeout'] = $snmp[2];
    $host_args['ping_retries'] = $snmp[3];
    $host_args['max_oids'] = $snmp[4];

    $host_args['auth_username'] = '';
    $host_args['auth_password'] = '';
    $host_args['auth_protocol'] = '';
    $host_args['priv_passphrase'] = '';
    $host_args['priv_protocol'] = '';
    $host_args['context'] = '';
    $host_args['engineid'] = '';
    $host_args['community'] = '';

    if ('3' === $host_args['version']) {
        $host_args['auth_username'] = $snmp[6];
        $host_args['auth_password'] = $snmp[7];
        $host_args['auth_protocol'] = $snmp[8];
        $host_args['priv_passphrase'] = $snmp[9];
        $host_args['priv_protocol'] = $snmp[10];
        $host_args['context'] = $snmp[11];
        $host_args['engineid'] = $snmp[12];
    } else {
        $host_args['community'] = $snmp[5];
    }

    $host_args['oids'] = [
        'entPhysicalName' => '.1.3.6.1.2.1.47.1.1.1.1.7',
        'cpmCPUTotalPhysicalIndex' => '.1.3.6.1.4.1.9.9.109.1.1.1.1.2',
        'cpmCPUTotal1minRev' => '.1.3.6.1.4.1.9.9.109.1.1.1.1.7',
        'cpmCPUTotal5minRev' => '.1.3.6.1.4.1.9.9.109.1.1.1.1.8',
    ];

    if ('index' == $cmd) {
        $arr_index = ss_cisco_cpu_reindex(cacti_snmp_walk(
            $hostname,
            $host_args['community'],
            $host_args['oids']['cpmCPUTotalPhysicalIndex'],
            $host_args['version'],
            $host_args['auth_username'],
            $host_args['auth_password'],
            $host_args['auth_protocol'],
            $host_args['priv_passphrase'],
            $host_args['priv_protocol'],
            $host_args['context'],
            $host_args['port'],
            $host_args['timeout'],
            $host_args['ping_retries'],
            $host_args['max_oids'],
            SNMP_POLLER,
            $host_args['engineid'],
        ));
        foreach ($arr_index as $index => $value) {
            echo $index."\n";
        }
    } elseif ('num_indexes' == $cmd) {
        $arr_index = ss_cisco_cpu_reindex(cacti_snmp_walk(
            $hostname,
            $host_args['community'],
            $host_args['oids']['cpmCPUTotalPhysicalIndex'],
            $host_args['version'],
            $host_args['auth_username'],
            $host_args['auth_password'],
            $host_args['auth_protocol'],
            $host_args['priv_passphrase'],
            $host_args['priv_protocol'],
            $host_args['context'],
            $host_args['port'],
            $host_args['timeout'],
            $host_args['ping_retries'],
            $host_args['max_oids'],
            SNMP_POLLER,
            $host_args['engineid'],
        ));

        return sizeof($arr_index);
    } elseif ('query' == $cmd) {
        switch ($arg1) {
            case 'cpuDesc':
                $arr = ss_cisco_cpu_get_desc($hostname, $host_args);
                break;

            case 'cpmCPUTotal1minRev':
            case 'cpmCPUTotal5minRev':
                $arr = ss_cisco_cpu_reindex(cacti_snmp_walk(
                    $hostname,
                    $host_args['community'],
                    $host_args['oids'][$arg1],
                    $host_args['version'],
                    $host_args['auth_username'],
                    $host_args['auth_password'],
                    $host_args['auth_protocol'],
                    $host_args['priv_passphrase'],
                    $host_args['priv_protocol'],
                    $host_args['context'],
                    $host_args['port'],
                    $host_args['timeout'],
                    $host_args['ping_retries'],
                    $host_args['max_oids'],
                    SNMP_POLLER,
                    $host_args['engineid'],
                ));
                break;
        }

        foreach ($arr as $index => $value) {
            echo $index.':'.$value."\n";
        }
    } elseif ('get' == $cmd) {
        $index = rtrim($arg2);

        switch ($arg1) {
            case 'cpuDesc':
                $arr = ss_cisco_cpu_get_desc($hostname, $host_args);

                if (isset($arr[$index])) {
                    return $arr[$index];
                }
                cacti_log('ERROR: Invalid Return Value in ss_cisco_cpu_usage.php for get ('.$arg1.') '.$index.' and host_id '.$host_id, false);

                return 'U';

                break;

            case 'cpmCPUTotal1minRev':
            case 'cpmCPUTotal5minRev':
                $value = cacti_snmp_get(
                    $hostname,
                    $host_args['community'],
                    $host_args['oids'][$arg1].'.'.$index,
                    $host_args['version'],
                    $host_args['auth_username'],
                    $host_args['auth_password'],
                    $host_args['auth_protocol'],
                    $host_args['priv_passphrase'],
                    $host_args['priv_protocol'],
                    $host_args['context'],
                    $host_args['port'],
                    $host_args['timeout'],
                    $host_args['ping_retries'],
                    SNMP_POLLER,
                    $host_args['engineid'],
                );

                if (!is_numeric($value)) {
                    cacti_log('ERROR: Invalid Return Value ['.$value.'] in ss_cisco_cpu_usage.php for get ('.$arg1.'), Index: '.$index.' and host_id '.$host_id, false);
                }

                return $value;
                break;
        }

        cacti_log('ERROR: Unable to determine get type in ss_cisco_cpu_usage.php for get ('.$arg1.') '.$index.' and host_id '.$host_id, false);

        return 'U';
    }
}

function ss_cisco_cpu_get_desc($hostname, $host_args)
{
    $hwidx_arr = ss_cisco_cpu_reindex(cacti_snmp_walk(
        $hostname,
        $host_args['community'],
        $host_args['oids']['cpmCPUTotalPhysicalIndex'],
        $host_args['version'],
        $host_args['auth_username'],
        $host_args['auth_password'],
        $host_args['auth_protocol'],
        $host_args['priv_passphrase'],
        $host_args['priv_protocol'],
        $host_args['context'],
        $host_args['port'],
        $host_args['timeout'],
        $host_args['ping_retries'],
        $host_args['max_oids'],
        SNMP_POLLER,
        $host_args['engineid'],
    ));

    $hw_arr = ss_cisco_cpu_reindex(cacti_snmp_walk(
        $hostname,
        $host_args['community'],
        $host_args['oids']['entPhysicalName'],
        $host_args['version'],
        $host_args['auth_username'],
        $host_args['auth_password'],
        $host_args['auth_protocol'],
        $host_args['priv_passphrase'],
        $host_args['priv_protocol'],
        $host_args['context'],
        $host_args['port'],
        $host_args['timeout'],
        $host_args['ping_retries'],
        $host_args['max_oids'],
        SNMP_POLLER,
        $host_args['engineid'],
    ));

    $return_arr = [];

    foreach ($name_arr as $index => $pool) {
        $hw_index = strstr($index, '.', true);
        if (isset($hw_arr[$hw_index]) && !empty($hw_arr[$hw_index])) {
            $cpu_description = preg_replace('/Virtual processor/i', 'VP', $hw_arr[$hw_index]);
        } else {
            $cpu_description = 'Unknown HW';
        }
        $return_arr[$index] = $cpu_description.' '.$pool;
    }

    foreach ($hwidx_arr as $index => $hw_index) {
        if (is_numeric($index)) {
            if (isset($hw_arr[$hw_index])) {
                // NCS-55xx devices
                //
                $hardware_desc = preg_replace('/Virtual processor/i', 'VP', $hw_arr[$hw_index]);
                if (null === $hardware_desc) {
                    $hardware_desc = $hw_arr[$hw_index];
                }
                $return_arr[$index] = $hardware_desc;
            } else {
                $return_arr[$index] = 'Unknown CPU '.$hw_index;
            }
        }
    }

    return $return_arr;
}

function ss_cisco_cpu_reindex($arr)
{
    $return_arr = [];
    for ($i = 0; $i < sizeof($arr); ++$i) {
        $index = substr($arr[$i]['oid'], strrpos($arr[$i]['oid'], '.') + 1);
        if (is_numeric($index)) {
            $return_arr[$index] = $arr[$i]['value'];
        }
    }

    return $return_arr;
}
