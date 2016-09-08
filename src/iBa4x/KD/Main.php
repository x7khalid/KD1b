<?php
namespace iBa4x\KD;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
class Main extends PluginBase implements Listener{
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getLogger()->info(TextFormat::YELLOW."By iBa4x");
		@mkdir($this->getDataFolder());
		$popup = [
			"Death Popup" => false,
			"kill Popup" => false,
		];
		$config = new Config($this->getDataFolder()."config.yml",Config::YAML,$popup);
		$config->save();
	}
	public function onJoin(PlayerJoinEvent $event){
		$name = $event->getPlayer()->getName();
		$kills = new Config($this->getDataFolder()."kills.yml",Config::YAML);
		$deaths = new Config($this->getDataFolder()."deaths.yml",Config::YAML);
		if($kills->get($name) == null){
			$kills->set($name,0);
			$kills->save();
		}
		if($deaths->get($name) == null){
			$deaths->set($name,0);
			$deaths->save();
		}
	}
	public function onDeath(PlayerDeathEvent $event){
		$entity = $event->getEntity();
		$cause = $entity->getLastDamageCause();
		if($entity instanceof Player){
			$name = $entity->getName();
			$deaths = new Config($this->getDataFolder()."deaths.yml",Config::YAML);
			$deaths->set($name,$deaths->get($name) + 1);
			$deaths->save();
			$config = new Config($this->getDataFolder()."config.yml",Config::YAML);
			if(!$config->get("Death Popup") == false){
				$entity->sendPopup(TextFormat::RED."Deaths +1");
			}
		}
		if($cause instanceof EntityDamageByEntityEvent){
			$killer = $event->getEntity()->getLastDamageCause()->getDamage();
			if($killer instanceof Player){
				$kills = new Config($this->getDataFolder()."kills.yml",Config::YAML);
				$name = $killer->getName();
				$kills->set($name,$kills->get($name) + 1);
				$kills->save();
				$config = new Config($this->getDataFolder()."config.yml",Config::YAML);
				if(!$config->get("kill Popup") == false){
					$killer->sendPopup(TextFormat::GREEN."Kills +1");
				}
			}
		}
	}
	public function onCommand(CommandSender $sender,Command $command, $label,array $args){
		switch($command->getName()){
			case "kills":
				$name = $sender->getPlayer()->getName();
				$sender->sendMessage("Kills : ".$this->getKills($name));
			return true;
			case "deaths":
				$name = $sender->getPlayer()->getName();
				$sender->sendMessage("Deaths : ".$this->getDeaths($name));
			return true;
			case "ratio":
				$name = $sender->getPlayer()->getName();
				$sender->sendMessage("Ratio : ".$this->getKills($name)/$this->getDeaths($name));
			return true;
			case "stats":
				$player = $sender->getPlayer();
				if($player->hasPermission("kd1b.command.stats")){
					$playern = array_shift($args);
					if($this->getServer()->getPlayer($playern)){
						$name = $this->getServer()->getPlayer($playern)->getName();
						$sender->sendMessage(TextFormat::GOLD." -+=KD1b=+-");
						$sender->sendMessage(TextFormat::GOLD."   Kills: ".TextFormat::RED.$this->getKills($name));
						$sender->sendMessage(TextFormat::GOLD."   Deaths: ".TextFormat::RED.$this->getDeaths($name));
						$sender->sendMessage(TextFormat::GOLD."   Ratio: ".TextFormat::RED.$this->getKills($name)/$this->getDeaths($name));
						$sender->sendMessage(TextFormat::GOLD." -+=".$name."=+-");
					}else{
						$sender->sendMessage(TextFormat::RED."Can't find player online.");
					}
				}else{
					$sender->sendMessage(TextFormat::RED."You do not have permission to use this command.");
				}
			return true;
		}
	}
	public function getKills($name){
		$kills = new Config($this->getDataFolder()."kills.yml",Config::YAML);
		return $kills->get($name);
	}
	public function getDeaths($name){
		$deaths = new Config($this->getDataFolder()."deaths.yml", Config::YAML);
		return $deaths->get($name);
	}
}
