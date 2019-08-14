#!/bin/bash

#
# Racktables Database Credentials
# should only be read only for this script
#

DB_ACCT="<db_user>"
DB_PASS="<db_pass>"

#
# Target nodes to update in xcat
# should be in format the xcat command nodels understands
#
NODES=$1

#
# Path to the xcat command nodels
#
NODELS_PATH="/opt/xcat/bin/"

#
# Script usage information
#
usage(){
    echo -e "rt_to_xcat_nodepos.sh will update the rack position and chassis information"
    echo -e "for all nodes in the specified group."
    echo -e "Usage:\n\trt_to_xcat_nodepos.sh <node or node_group>"
}

#
# Update xcat
#
update_xcat(){
    for x in $(${NODELS_PATH}/nodels ${NODES}); do
        object=$(mysql -u ${DB_ACCT} -p${DB_PASS} -ss -e "SELECT id FROM racktables.RackObject WHERE name='${x}'")
        rack=$(mysql -u ${DB_ACCT} -p${DB_PASS} -ss -e "SELECT rack_id FROM racktables.RackSpace WHERE object_id='${object}'" | uniq)

        count=0
        for y in $rack; do
            let count++
        done

        if [[ "${count}" == "0" ]]; then
            echo "Error! Object not mounted to a rack in Racktables: ${x}"
        elif [[ "${count}" != "1" ]]; then
            echo "Error! Object cannot be located in more than 1 rack: ${x}"
        else
            rack_name=$(mysql -u ${DB_ACCT} -p${DB_PASS} -ss -e "SELECT name FROM racktables.Rack WHERE id='${rack}'")
            if [[ "${rack_name}" != "" ]]; then
                nodech ${x} nodepos.rack="${rack_name}"
            fi
            units=$(mysql -u ${DB_ACCT} -p${DB_PASS} -ss -e "SELECT unit_no FROM racktables.RackSpace WHERE object_id='${object}'" | sort | uniq)
            count=0
            MULTUNITS=0
            for y in ${units}; do
                if [[ "${count}" == "0" ]]; then
                    first=${y}
                fi
                let count++
                last=${y}
            done

            if [[ "${count}" == "0" ]]; then
                echo "Error! Unable to get occupying units from Racktables: ${x}"
            elif [[ "${count}" == "1" ]]; then
                RACKUNITS="${last}"
                nodech ${x} nodepos.u="${RACKUNITS}"
            elif [[ "${count}" != "1" ]]; then
                MULTUNITS=1
                RACKUNITS="${first}-${last}"
                nodech $x nodepos.u="${RACKUNITS}"
            fi
        fi

        parent=$(mysql -u ${DB_ACCT} -p${DB_PASS} -ss -e "SELECT parent_entity_id FROM racktables.EntityLink WHERE child_entity_id='${object}'")
        if [[ "${parent}" != "" ]]; then
            parent_name=$(mysql -u ${DB_ACCT} -p${DB_PASS} -ss -e "SELECT name FROM racktables.RackObject WHERE id='${parent}'")
            nodech $x nodepos.chassis="${parent_name}"
            if [ ${MULTUNITS} -gt 1 ]; then
                echo -e " ${x} |${rack_name} |${RACKUNITS}\t|  ${parent_name}"
            else
                echo -e " ${x} |${rack_name} |  ${RACKUNITS}\t|  ${parent_name}"
            fi
        else
            echo -e " ${x} |${rack_name} |  ${RACKUNITS}\t|"
        fi
    done
}

if [ $# -eq 0 ]; then
    usage
    exit 1
fi

if [ $# -gt 1 ]; then
    usage
    exit 1
fi

echo -e "  OBJECT  | RACK | UNIT |      CHASSIS\n--------------------------------------------------" 
update_xcat
