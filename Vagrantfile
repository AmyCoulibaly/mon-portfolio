Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/jammy64"

  # --- VM 1 : FIREWALL ---
  config.vm.define "firewall" do |fw|
    fw.vm.hostname = "firewall"
    fw.vm.network "private_network", ip: "192.168.100.1", virtualbox__intnet: "dmz-net"
    fw.vm.network "private_network", ip: "192.168.10.1", virtualbox__intnet: "lan-net"
    
    fw.vm.provision "shell", inline: <<-SHELL
      echo "--- Configuration des règles de sécurité ---"
      sysctl -w net.ipv4.ip_forward=1
      echo "net.ipv4.ip_forward=1" >> /etc/sysctl.conf

      # 1. Nettoyage
      iptables -F
      iptables -t nat -F

      # 2. POLITIQUE PAR DÉFAUT : TOUT BLOQUER (Sécurité max)
      iptables -P FORWARD DROP
      iptables -P INPUT ACCEPT
      iptables -P OUTPUT ACCEPT

      # --- RÈGLE 1 : Autoriser le retour de trafic (Indispensable) ---
      iptables -A FORWARD -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT

      # --- RÈGLE 2 : Serveur WEB accède à INTERNET (mais PAS à la DB) ---
      # On autorise le Web (100.10) à sortir par l'interface NAT (eth0)
      iptables -A FORWARD -s 192.168.100.10 -o eth0 -j ACCEPT

      # --- RÈGLE 3 : Serveur DB accède au WEB et à INTERNET ---
      iptables -A FORWARD -s 192.168.10.10 -j ACCEPT

      # --- RÈGLE 4 : INTERNET accède au Serveur WEB (Port 80) ---
      iptables -t nat -A PREROUTING -p tcp --dport 80 -j DNAT --to-destination 192.168.100.10:80
      iptables -A FORWARD -d 192.168.100.10 -p tcp --dport 80 -j ACCEPT

      # --- NAT (Masquerade pour que tout le monde puisse sortir) ---
      iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE

      # DNS
      echo "nameserver 8.8.8.8" > /etc/resolv.conf
    SHELL
  end

  # --- VM 2 : WEB SERVER ---
  config.vm.define "webserver" do |web|
    web.vm.hostname = "web-server"
    web.vm.network "private_network", ip: "192.168.100.10", virtualbox__intnet: "dmz-net"
    web.vm.network "forwarded_port", guest: 80, host: 8080
    
    web.vm.provision "shell", inline: <<-SHELL
      ip route del default || true
      ip route add default via 192.168.100.1
      echo "nameserver 8.8.8.8" > /etc/resolv.conf
    SHELL
  end

  # --- VM 3 : DB SERVER ---
  config.vm.define "dbserver" do |db|
    db.vm.hostname = "db-server"
    db.vm.network "private_network", ip: "192.168.10.10", virtualbox__intnet: "lan-net"
    
    db.vm.provision "shell", inline: <<-SHELL
      ip route del default || true
      ip route add default via 192.168.10.1
      echo "nameserver 8.8.8.8" > /etc/resolv.conf
    SHELL
  end
end