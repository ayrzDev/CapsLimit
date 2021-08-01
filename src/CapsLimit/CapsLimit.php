<?php

/*
 * CapsLimit (v1.0.0)
 * Developer: Ayrz
 * Website: www.minerwox.xyz
 * Licensed under MIT (https://github.com/AyrzC/CapsLimit/blob/main/LICENSE)
 */

namespace CapsLimit;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class CapsLimit extends PluginBase implements Listener{

    private $maxcaps;
    private $prefix;
    public $messages;
    public $messageConfig;
    private $langs = ["tr_TR","en_US"];
    
    private static $instance;
    public function onLoad() {
        self::$instance = $this;
    }
    public function onEnable(){
        $this->prefix = $this->getConfig()->get("prefix");
        $this->loadConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info($this->prefix."Maximum caps limited to ".$this->maxcaps);

        $this->saveResource("langs/".$this->getConfig()->getNested("lang").".yml");
        $this->messageConfig = new Config($this->getDataFolder()."langs/".$this->getConfig()->getNested("lang").".yml");
        $this->messages = $this->messageConfig->getAll();
    }

    public function onDisable(){
        $this->saveConfig();
    }

    /**
     *  Config Load
     */
    public function loadConfig(){
        $this->saveDefaultConfig();
        $this->maxcaps = intval($this->getConfig()->get("max-caps"));
    }

     /**
     *  Config Save
     */
    public function saveConfig(){
        $this->getConfig()->set("max-caps", $this->maxcaps);
        $this->getConfig()->save();
    }

    
    public static function Text(string $key, array $args = []): ?string {
        return self::styleText(self::$instance->messages[$key] ?? "Language error: crashed file.", $args);
    }

    public static function styleText(string $message, array $args = []): string {
        return str_replace(
            [
                "\\n",
                "{line}",
                "&"
            ],
            [
                "\n",
                "\n",
                "§"
            ],
            str_replace(
                array_map(function($n){return "%".(int)$n;}, array_keys($args)),
                array_values($args),
                $message
            )
        );
    }

    public function onCommand(CommandSender $sender, Command $command, $commandAlias, array $args): bool{
        switch($command->getName()){
          case "capslimit":
            if(!$sender->hasPermission("capslimit.set")){
            return false;
          }
          if(!is_array($args) or count($args) < 1){
            $sender->sendMessage($this->prefix.self::Text("use-command"));
            return true;
          }
          if (!is_array($args) or is_numeric($args[0]) > 0){
            $this->maxcaps = $args[0];
            $sender->sendMessage($this->prefix.self::Text("max-caps").$this->maxcaps);
            $this->saveConfig();
            return true;
          }
            $sender->sendMessage($this->prefix.self::Text("must-number"));
            return false;
            break;   
        }
    }
    
    public function onChat(PlayerChatEvent $event){
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $strlen = strlen($message);
        $asciiA = ord("A");
        $asciiZ = ord("Z");
        $count = 0;
        for($i = 0; $i < $strlen; $i++){
          $char = $message[$i];
          $ascii = ord($char);
            if($asciiA <= $ascii and $ascii <= $asciiZ){
             $count++;
            }
        }
        if ($count > $this->maxcaps) {
            $event->setCancelled(true);
            $player->sendMessage($this->prefix.TextFormat::RED.self::Text("chat-message"));
        }
    }
}
