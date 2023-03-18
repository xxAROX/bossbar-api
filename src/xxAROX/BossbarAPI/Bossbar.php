<?php
declare(strict_types=1);
namespace xxAROX\BossbarAPI;
use Closure;
use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use GlobalLogger;
use JetBrains\PhpStorm\Pure;
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
	protected int $bossActorId = -1;
	protected ?EntityMetadataCollection $metadataCollection = null;
	protected ?Closure $textHandler = null;
	/** @var \WeakMap<Player, true> */
	protected \WeakMap $players;

	/**
	 * Function getPlayersAsArray
	 * @return array<Player>
	 */
	#[Pure]
	private function getPlayersAsArray(): array{
		$players = [];
		foreach ($this->players as $player => $bool) {
			if ($player instanceof Player) $players[$player->getId()] = $player;
		}
		return $players;
	}

	/**
	 * Bossbar constructor.
	 * @param string $title
	 * @param float $percentage
	 * @param null|BossbarColor $color
	 * @param bool $darkenScreen
	 */
	function __construct(
		protected string $title = "",
		protected float $percentage = 1.0,
		protected ?BossbarColor $color = null,
		protected bool $darkenScreen = false
	){
		$this->players = new \WeakMap();
		$this->bossActorId = Entity::nextRuntimeId();
		$this->initializeMetadataCollection();
		$this->percentage = max(min(1.0, $this->percentage), 0);
		$this->color = $color ?? BossbarColor::PURPLE();
		$this->textHandler = fn (Player $player, string $raw): string => $raw;
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
	 * @param null|Vector3 $vector3
	 * @return AddActorPacket
	 */
	private function initializeActorPacket(?Vector3 $vector3 = null): AddActorPacket{
		return AddActorPacket::create(
			$this->bossActorId,
			$this->bossActorId,
			EntityIds::SLIME,
			$vector3 ?? Vector3::zero(),
			null,
			0.0,
			0.0,
			0.0,
			0.0,
			[ new Attribute(\pocketmine\entity\Attribute::HEALTH, 0.0, 100.0, 100.0, 100.0, []) ],
			$this->metadataCollection->getAll(),
			new PropertySyncData([], []),
			[]
		);
	}

	/**
	 * Function getTitle
	 * @return string
	 */
	public function getTitle(): string{
		return $this->title;
	}
	/**
	 * Function getPercentage
	 * @return float
	 */
	public function getPercentage(): float{
		return $this->percentage;
	}
	/**
	 * Function getColor
	 * @return ?BossbarColor
	 */
	public function getColor(): ?BossbarColor{
		return $this->color;
	}
	/**
	 * Function getDarkenScreen
	 * @return bool
	 */
	public function getDarkenScreen(): bool{
		return $this->darkenScreen;
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
	/**
	 * Function setTitle
	 * @param string $title
	 * @return Bossbar
	 */
	public function setTitle(string $title): Bossbar{
		$this->title = $title;
		foreach ($this->getPlayersAsArray() as $player) {
			if ($player->isConnected()) $player->getNetworkSession()->sendDataPacket(BossEventPacket::title($this->bossActorId, $this->textHandler->call($this, $player, $this->title)));
		}
		return $this;
	}
	/**
	 * Function setPercentage
	 * @param float $percentage
	 * @return Bossbar
	 */
	public function setPercentage(float $percentage): Bossbar{
		$this->percentage = $percentage;
		Server::getInstance()->broadcastPackets($this->getPlayersAsArray(), [BossEventPacket::healthPercent($this->bossActorId, $this->percentage)]);
		return $this;
	}
	/**
	 * Function setColor
	 * @param null|BossbarColor $color
	 * @return Bossbar
	 */
	public function setColor(?BossbarColor $color): Bossbar{
		$this->color = $color;
		Server::getInstance()->broadcastPackets($this->getPlayersAsArray(), [BossEventPacket::properties($this->bossActorId, $this->darkenScreen, $this->color->color())]);
		return $this;
	}
	/**
	 * Function setDarkenScreen
	 * @param bool $darkenScreen
	 * @return Bossbar
	 */
	public function setDarkenScreen(bool $darkenScreen): Bossbar{
		$this->darkenScreen = $darkenScreen;
		Server::getInstance()->broadcastPackets($this->getPlayersAsArray(), [BossEventPacket::properties($this->bossActorId, $this->darkenScreen, $this->color->color())]);
		return $this;
	}

	/**
	 * Function hide
	 * @param Player $player
	 * @return Bossbar
	 */
	public function hide(Player $player): Bossbar{
		if ($this->includesPlayer($player)) $player->getNetworkSession()->sendDataPacket(BossEventPacket::hide($this->bossActorId));
		return $this;
	}
	/**
	 * Function show
	 * @param Player $player
	 * @return Bossbar
	 */
	public function show(Player $player): Bossbar{
		if (!$player->getNetworkSession()->isConnected()) return $this;
		if ($this->includesPlayer($player)) $player->getNetworkSession()->sendDataPacket(BossEventPacket::show($this->bossActorId, $this->textHandler->call($this, $player, $this->title), $this->percentage));
		else $this->addPlayer($player);
		return $this;
	}

	/**
	 * Function includesPlayer
	 * @param Player $player
	 * @return bool
	 */
	public function includesPlayer(Player $player): bool{
		return $this->players->offsetExists($player);
	}
	/**
	 * Function addPlayer
	 * @param Player $player
	 * @return Bossbar
	 */
	public function addPlayer(Player $player): Bossbar{
		if (!$player->getNetworkSession()->isConnected()) return $this;
		if ($this->includesPlayer($player)) $player->getNetworkSession()->sendDataPacket(BossEventPacket::hide($this->bossActorId));
		else $player->getNetworkSession()->sendDataPacket($this->initializeActorPacket($player->getPosition()->asVector3()));
		if (!$this->includesPlayer($player)) $this->players->offsetSet($player, true);
		$player->getNetworkSession()->sendDataPacket(BossEventPacket::show($this->bossActorId, $this->textHandler->call($this, $player, $this->title), $this->percentage));
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
	 * Function removePlayer
	 * @param Player $player
	 * @return Bossbar
	 */
	public function removePlayer(Player $player): Bossbar{
		if (!$player->getNetworkSession()->isConnected()) return $this;
		if ($this->includesPlayer($player)) {
			$player->getNetworkSession()->sendDataPacket(BossEventPacket::hide($this->bossActorId));
			$this->players->offsetUnset($player);
		}
		return $this;
	}
	/**
	 * Function removeAllPlayers
	 * @return Bossbar
	 */
	public function removeAllPlayers(): Bossbar{
		Server::getInstance()->broadcastPackets($this->players, [ BossEventPacket::hide($this->bossActorId) ]);
		unset($this->players);
		$this->players = new \WeakMap();
		return $this;
	}
}
