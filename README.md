# bossbar-api
My style of an OO bossbar-api for pmmp   |   codebase from: sky-min/bossbarapi


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