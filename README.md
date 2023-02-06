# bossbar-api
My style of an oo bossbar-api for pmmp   |   codebase from: sky-min/bossbarapi

```composer install xxarox/bossbar-api```

<details>
<summary>How to use?</summary>

```php
use xxAROX\BossbarAPI\{Bossbar,BossbarColor};

$bossBar = new Bossbar(
	"Space suits yell with faith!",
	1.0,
	/** @var BossbarColor */ BossbarColor::RED(),
	/** @var bool */ false,
	/** @var null|Vector3 */ $game->getMiddleVector3()
);
// add players
$bossBar->addAllPlayers();
$bossBar->addPlayer(Player::class);

// remove players
$bossBar->removeAllPlayers();
$bossBar->removePlayer(Player::class);

// check player
$bossBar->includesPlayer(Player::class);

// update title
$bossBar->setTitle("Warp patiently like a cloudy collective.\n\nWhere is the ancient cosmonaut?");

// update color
$bossBar->setColor(\xxAROX\BossbarAPI\BossbarColor::YELLOW());

// update percentage
$bossBar->setPercentage($bossBar->getPercentage() -.01);

// update darken screen (idk what this is)
$bossBar->setDarkenScreen(!$bossBar->getDarkenScreen());

// and this also works
$bossBar
	->setTitle("View without mystery, and we won’t handle a captain.")
	->setPercentage(0)
	->setColor(BossbarColor::BLUE())
;
```

</details>

<details>
<summary>How to use with a language-api?</summary>

```php
use xxAROX\BossbarAPI\{Bossbar,BossbarColor};

$bossBar = new Bossbar(
	"bossbar.title.example",
	1.0,
	/** @var BossbarColor */ BossbarColor::RED(),
	/** @var bool */ false,
	/** @var null|Vector3 */ $game->getMiddleVector3()
);
// first add a translation handler of your choice
$bossBar->setTextHandler(fn (\pocketmine\player\Player $player, string $raw) => $player->getLanguage()->translate(new \pocketmine\lang\Translatable($raw)));

// add players
$bossBar->addAllPlayers();

// update title
$bossBar->setTitle("my.language.key");
```

</details>