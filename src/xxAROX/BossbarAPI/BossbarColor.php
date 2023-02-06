<?php
declare(strict_types=1);
namespace xxAROX\BossbarAPI;
use pocketmine\utils\EnumTrait;
use pocketmine\network\mcpe\protocol\types\BossBarColor as PMMPBossBarColor;


/**
 * Class BossbarColors
 * @package xxAROX\BossbarAPI
 * @author Jan Sohn / xxAROX
 * @date 06. February, 2023 - 15:54
 * @ide PhpStorm
 * @project bossbar-api
 */

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see building/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static BossbarColor BLUE()
 * @method static BossbarColor GREEN()
 * @method static BossbarColor PINK()
 * @method static BossbarColor PURPLE()
 * @method static BossbarColor RED()
 * @method static BossbarColor WHITE()
 * @method static BossbarColor YELLOW()
 */
class BossbarColor{
	use EnumTrait{
		__construct as ___Enum__construct;
	}


	/**
	 * Function setup
	 * @return void
	 */
	protected static function setup(): void{
		self::registerAll(
			new self("pink", PMMPBossBarColor::PINK),
			new self("blue", PMMPBossBarColor::BLUE),
			new self("red", PMMPBossBarColor::RED),
			new self("green", PMMPBossBarColor::GREEN),
			new self("yellow", PMMPBossBarColor::YELLOW),
			new self("purple", PMMPBossBarColor::PURPLE),
			new self("white", PMMPBossBarColor::WHITE),
		);
	}

	/**
	 * BossbarColor constructor.
	 * @param string $name
	 * @param int $color
	 */
	public function __construct(string $name, protected int $color){
		$this->___Enum__construct($name);
	}

	/**
	 * Function color
	 * @return int
	 */
	public function color(): int{
		return $this->color;
	}
}
