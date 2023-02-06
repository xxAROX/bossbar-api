<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/building/generate-registry-annotations.php";
var_dump(class_exists(\xxAROX\BossbarAPI\BossbarColor::class));
generateRegistryAnnotations(__DIR__ . "/src/xxAROX/BossbarAPI/BossbarColor.php");
