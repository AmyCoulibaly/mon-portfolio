Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/jammy64"

  # --- VM 1 : FIREWALL ---
  config.vm.define "firewall" do |fw|
    fw.vm.hostname = "firewall"
    fw.vm.network "private_network", ip: "192.168.100.1", virtualbox__intnet: "dmz-net"
    fw.vm.network "private_network", ip: "192.168.10.1", virtualbox__intnet: "lan-net"
    
    fw.vm.provision "shell", inline: <<-SHELL
      echo "--- Configuration du Firewall ---"
      
      # 1. Activation du routage au niveau du noyau
      sysctl -w net.ipv4.ip_forward=1
      echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf

      # 2. Nettoyage complet
      iptables -F
      iptables -t nat -F

      # 3. POLITIQUES PAR DÉFAUT
      iptables -P FORWARD DROP
      iptables -P INPUT ACCEPT
      iptables -P OUTPUT ACCEPT

      # --- RÈGLES DE FLUX ---

      # Autoriser le trafic de retour (Indispensable pour que le Web reçoive les données d'Internet)
      iptables -A FORWARD -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT

      # Règle 4 : Serveur Web accède à Internet (pour Ansible et mises à jour)
      iptables -A FORWARD -s 192.168.100.10 -j ACCEPT

      # Règle 2 : DB Server accède au Web et à Internet
      iptables -A FORWARD -s 192.168.10.10 -j ACCEPT

      # Règle 3 : Internet accède au Serveur Web (DNAT Port 80)
      iptables -t nat -A PREROUTING -p tcp --dport 80 -j DNAT --to-destination 192.168.100.10:80
      iptables -A FORWARD -d 192.168.100.10 -p tcp --dport 80 -j ACCEPT

      # 4. NAT (Masquerade sur eth0 - l'interface NAT de Vagrant)
      iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE

      # 5. DNS et Persistance
      echo "nameserver 8.8.8.8" > /etc/resolv.conf
      # On installe iptables-persistent sans demander confirmation
      export DEBIAN_FRONTEND=noninteractive
      apt-get update && apt-get install -y iptables-persistent
      netfilter-persistent save
    SHELL
  end

  # --- VM 2 : WEB SERVER ---
  config.vm.define "webserver" do |web|
    web.vm.hostname = "web-server"
    web.vm.network "private_network", ip: "192.168.100.10", virtualbox__intnet: "dmz-net"
    web.vm.network "forwarded_port", guest: 80, host: 8080
    # Forcer le DNS pour Ansible
    web.vm.provision "shell", inline: "echo 'nameserver 8.8.8.8' > /etc/resolv.conf"
  end

  # --- VM 3 : DB SERVER ---
  config.vm.define "dbserver" do |db|
    db.vm.hostname = "db-server"
    db.vm.network "private_network", ip: "192.168.10.10", virtualbox__intnet: "lan-net"
    # Forcer le DNS
    db.vm.provision "shell", inline: "echo 'nameserver 8.8.8.8' > /etc/resolv.conf"
  end

  config.vm.provider "virtualbox" do |vb|
    vb.memory = "1024"
    vb.cpus = 1
  end
end
