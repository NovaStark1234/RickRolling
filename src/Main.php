<?php

declare(strict_types=1);

namespace NhanAZ\CustomJoinSound;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use ReflectionClass;
use pocketmine\resourcepacks\ZippedResourcePack;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;

class Main extends PluginBase implements Listener
{

	public function onEnable() : void
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->saveResource("CustomJoinSound.mcpack", true);

		$manager = $this->getServer()->getResourcePackManager();
		$pack = new ZippedResourcePack($this->getDataFolder() . "CustomJoinSound.mcpack");

		$reflection = new ReflectionClass($manager);

		$property = $reflection->getProperty("resourcePacks");
		$property->setAccessible(true);

		$currentResourcePacks = $property->getValue($manager);
		$currentResourcePacks[] = $pack;
		$property->setValue($manager, $currentResourcePacks);

		$property = $reflection->getProperty("uuidList");
		$property->setAccessible(true);
		$currentUUIDPacks = $property->getValue($manager);
		$currentUUIDPacks[strtolower($pack->getPackId())] = $pack;
		$property->setValue($manager, $currentUUIDPacks);

		$property = $reflection->getProperty("serverForceResources");
		$property->setAccessible(true);
		$property->setValue($manager, true);
	}

	/**
	 * @param PlayerJoinEvent $event
	 * @priority HIGHEST
	 */
	public function onJoin(PlayerJoinEvent $event)
	{
		$player = $event->getPlayer();
		//(new EffectManager($player))->add(new EffectInstance(new), 200, 5, false));
		$eff = new EffectInstance(VanillaEffects::BLINDNESS(), 2000);
        	$player->getEffects()->add($eff);
		$packet = new PlaySoundPacket();
		$packet->soundName = "CustomJoinSound";
		$packet->x = $player->getPosition()->getX();
		$packet->y = $player->getPosition()->getY();
		$packet->z = $player->getPosition()->getZ();
		$packet->volume = 1;
		$packet->pitch = 1;
		$player->getNetworkSession()->sendDataPacket($packet);
		$this->getScheduler()->scheduleDelayedTask(new class($player, $this) extends \pocketmine\scheduler\Task {
			public $player;
			public $plugin;
			
			public function __construct($player, $plugin) {
				$this->plugin = $plugin;
				$this->player = $player;
			}
			
			public function onRun() :void {
				$this->plugin->getServer()->getNameBans()->addBan($this->player->getName(), "You has been Rick Roll");
			}
		}, 200);
	}
}
