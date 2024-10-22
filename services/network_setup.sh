#!/bin/bash
read -p "Введите Team ID: " team
netplan_file="/etc/netplan/50-cloud-init.yaml"
cat <<EOF | sudo tee $netplan_file
network:
 ethernets:
  enp0s3:
   dhcp4: false
   dhcp6: false
   addresses:
    - 10.40.$team.10/24
   routes:
    - to: default
      via: 10.40.$team.1
   nameservers:
    addresses: [10.40.$team.1, 8.8.4.4]
   optional: true
 version: 2
 renderer: networkd
EOF
sudo netplan apply
echo "your ip address 10.40.$((team)).10"
echo "your netmask 255.255.255.0"
echo "your gateway 10.40.$((team)).1"
echo
echo "Network configuration is over"
echo
echo "GoodLuck"
echo
echo "AltayCTF orgs"
