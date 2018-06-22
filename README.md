# Cisco CPU and Memory Utilisation

Graphs for Cisco memory and CPU utilisation per line card for Cacti network monitoring. Includes graphs and data source templates. Uses script servers to gather the data from SNMP enabled devices.

## Notes

- CPU and Memory graphs are separate graphs and templates. You can import both or just one as needed.
- Memory graphs are based on CISCO-ENHANCED-MEMPOOL-MIB using 64bit counters (cempMemPoolHCFree and cempMemPoolHCUsed) for memory over 4GB. Newer cisco devices generally support this. For details: https://iphostmonitor.com/mib/CISCO-ENHANCED-MEMPOOL-MIB.html

## Installation

- Copy scripts/ss_cisco_* to your cacti scripts directory <cacti_install_path>/scripts/
- Copy resource/script_server/cisco* to cacti resource directory <cacti_install_path>/resource/script_server/
- Import templates in the template directory using the Cacti Web UI.
