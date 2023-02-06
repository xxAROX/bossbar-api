<?php
declare(strict_types=1);
namespace xxAROX\BossbarAPI;
use Closure;
use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use GlobalLogger;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\types\entity\Attribute;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Utils;
use Throwable;


/**
 * Class Bossbar
 * @package xxAROX\BossbarAPI
 * @author Jan Sohn / xxAROX
 * @date 06. February, 2023 - 15:53
 * @ide PhpStorm
 * @project bossbar-api
 */
class Bossbar{
	/** @var array<Player> */
	protected array $players = [];
	protected ?EntityMetadataCollection $metadataCollection = null;
	protected ?AddActorPacket $actorPacket = null;
	protected int $bossActorId = -1;
	protected ?Closure $textHandler = null;

	/**
	 * Bossbar constructor.
	 * @param string $title
	 * @param float $percentage
	 * @param null|BossbarColor $color
	 * @param bool $darkenScreen
	 * @param null|Vector3 $vector3
	 */
	function __construct(
		protected string $title = "",
		protected float $percentage = 1.0,
		protected ?BossbarColor $color = null,
		protected bool $darkenScreen = false,
		protected ?Vector3 $vector3 = null
	){
		$this->initializeMetadataCollection();
		$this->initializeActorPacket();
		$this->percentage = max(min(1.0, $this->percentage), 0);
		$this->color ??= BossbarColor::PURPLE();
		$this->vector3 ??= Vector3::zero();
		$this->bossActorId = Entity::nextRuntimeId();
	}

	/**
	 * Function initializeMetadataCollection
	 * @return void
	 */
	private function initializeMetadataCollection(): void{
		if (!is_null($this->metadataCollection)) return;
		$this->metadataCollection = new EntityMetadataCollection();
		$this->metadataCollection->setGenericFlag(EntityMetadataFlags::FIRE_IMMUNE, true);
		$this->metadataCollection->setGenericFlag(EntityMetadataFlags::SILENT, true);
		$this->metadataCollection->setGenericFlag(EntityMetadataFlags::INVISIBLE, true);
		$this->metadataCollection->setGenericFlag(EntityMetadataFlags::NO_AI, true);
		$this->metadataCollection->setString(EntityMetadataProperties::NAMETAG, "");
		$this->metadataCollection->setFloat(EntityMetadataProperties::SCALE, 0.0);
		$this->metadataCollection->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
		$this->metadataCollection->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0.0);
		$this->metadataCollection->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0.0);
	}

	/**
	 * Function initializeActorPacket
	 * @return void
	 */
	private function initializeActorPacket(): void{
		if (!is_null($this->actorPacket)) return;
		$this->actorPacket = AddActorPacket::create($this->bossActorId, $this->bossActorId, EntityIds::SLIME, Vector3::zero(), null, 0.0, 0.0, 0.0, 0.0, [ new Attribute(\pocketmine\entity\Attribute::HEALTH, 0.0, 100.0, 100.0, 100.0, []) ], $this->metadataCollection->getAll(), new PropertySyncData([], []), []);
	}

	/**
	 * Function setTitle
	 * @param string $title
	 * @return Bossbar
	 */
	public function setTitle(string $title): Bossbar{
		$this->title = $title;
		foreach($this->players as $player) $player->getNetworkSession()->sendDataPacket(BossEventPacket::title($this->bossActorId, $this->textHandler->call($this, $player, $this->title)));
		return $this;
	}

	/**
	 * Function getTitle
	 * @return string
	 */
	public function getTitle(): string{
		return $this->title;
	}

	/**
	 * Function setPercentage
	 * @param float $percentage
	 * @return Bossbar
	 */
	public function setPercentage(float $percentage): Bossbar{
		$this->percentage = $percentage;
		Server::getInstance()->broadcastPackets($this->players, [ BossEventPacket::healthPercent($this->bossActorId, $this->percentage) ]);
		return $this;
	}

	/**
	 * Function getPercentage
	 * @return float
	 */
	public function getPercentage(): float{
		return $this->percentage;
	}

	/**
	 * Function setColor
	 * @param null|BossbarColor $color
	 * @return Bossbar
	 */
	public function setColor(?BossbarColor $color): Bossbar{
		$this->color = $color;
		Server::getInstance()->broadcastPackets($this->players, [ BossEventPacket::properties($this->bossActorId, $this->darkenScreen, $this->color->color()) ]);
		return $this;
	}

	/**
	 * Function getColor
	 * @return ?BossbarColor
	 */
	public function getColor(): ?BossbarColor{
		return $this->color;
	}

	/**
	 * Function setDarkenScreen
	 * @param bool $darkenScreen
	 * @return Bossbar
	 */
	public function setDarkenScreen(bool $darkenScreen): Bossbar{
		$this->darkenScreen = $darkenScreen;
		Server::getInstance()->broadcastPackets($this->players, [ BossEventPacket::properties($this->bossActorId, $this->darkenScreen, $this->color->color()) ]);
		return $this;
	}

	/**
	 * Function getDarkenScreen
	 * @return bool
	 */
	public function getDarkenScreen(): bool{
		return $this->darkenScreen;
	}

	/**
	 * Function includesPlayer
	 * @param Player $player
	 * @return bool
	 */
	public function includesPlayer(Player $player): bool{
		return isset($this->players[spl_object_id($player)]);
	}

	/**
	 * Function addPlayer
	 * @param Player $player
	 * @return Bossbar
	 */
	public function addPlayer(Player $player): Bossbar{
		$this->players[spl_object_id($player)] = $player;
		Server::getInstance()->broadcastPackets([ $player ], [ BossEventPacket::show($this->bossActorId, $this->textHandler->call($this, $player, $this->title), $this->percentage) ]);
		return $this;
	}

	/**
	 * Function removePlayer
	 * @param Player $player
	 * @return Bossbar
	 */
	public function removePlayer(Player $player): Bossbar{
		if (isset($this->players[spl_object_id($player)])) unset($this->players[spl_object_id($player)]);
		Server::getInstance()->broadcastPackets([ $player ], [ BossEventPacket::hide($this->bossActorId) ]);
		return $this;
	}

	/**
	 * Function addAllPlayers
	 * @return Bossbar
	 */
	public function addAllPlayers(): Bossbar{
		foreach (Server::getInstance()->getOnlinePlayers() as $player) $this->addPlayer($player);
		return $this;
	}

	/**
	 * Function removeAllPlayers
	 * @return Bossbar
	 */
	public function removeAllPlayers(): Bossbar{
		Server::getInstance()->broadcastPackets($this->players, [ BossEventPacket::hide($this->bossActorId) ]);
		unset($this->players);
		$this->players = [];
		return $this;
	}

	/**
	 * Function setTextHandler
	 * @param null|Closure $textHandler Closure(Player $player, string $raw): string
	 * @return Bossbar
	 */
	public function setTextHandler(?Closure $textHandler): Bossbar{
		try {Utils::validateCallableSignature(new CallbackType(
			new ReturnType("string"),
			new ParameterType("player", Player::class),
			new ParameterType("raw", "string")
		), $textHandler);}
		catch (Throwable $e) {GlobalLogger::get()->logException($e);}
		$this->textHandler = $textHandler;
		return $this;
	}
}
