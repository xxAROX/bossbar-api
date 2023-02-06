<?php
declare(strict_types=1);
namespace xxAROX\BossbarAPI;
use pocketmine\utils\EnumTrait;


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
			new self("pink", 0),
			new self("blue", 1),
			new self("red", 2),
			new self("green", 3),
			new self("yellow", 4),
			new self("purple", 5),
			new self("white", 6),
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
