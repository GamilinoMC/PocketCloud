<?php

namespace pocketcloud\update;

use pocketcloud\language\Language;
use pocketcloud\util\AsyncExecutor;
use pocketcloud\util\CloudLogger;
use pocketcloud\util\SingletonTrait;
use pocketcloud\util\VersionInfo;

class UpdateChecker {
    use SingletonTrait;

    private array $data = [];

    public function __construct() {
        self::setInstance($this);
    }

    public function check(): void {
        AsyncExecutor::execute(function(): false|string {
            try {
                $ch = curl_init("https://api.github.com/repos/PocketCloudSystem/PocketCloud/releases/latest");
                curl_setopt_array($ch, [
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HEADER => false,
                        CURLOPT_USERAGENT => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)"
                    ]
                );

                $result = curl_exec($ch);
                $data = json_decode($result, true, flags: JSON_THROW_ON_ERROR);
                if ($data === false || $data === null) {
                    return false;
                } else {
                    if (isset($data["tag_name"])) {
                        return $data["tag_name"];
                    } else {
                        return false;
                    }
                }
            } catch (\JsonException $e) {
                CloudLogger::get()->exception($e);
                return false;
            }
        }, function(null|string|false $result): void {
            if ($result === false || $result === null) {
                if (Language::current() === Language::ENGLISH()) CloudLogger::get()->error("§cError occurred while checking for new updates!");
                else CloudLogger::get()->error("§cEin Fehler ist während der Überprüfung von neuen Updates aufgetreten!");
            } else {
                $current = explode(".", UpdateChecker::getInstance()->getCurrentVersion());
                $latest = explode(".", $result);
                $outdated = false;
                $highVersion = false;

                $i = 0;
                foreach ($current as $number) {
                    if (intval($latest[$i]) > intval($number)) {
                        $outdated = true;
                        break;
                    } else if (intval($number) > intval($latest[$i])) {
                        $highVersion = !VersionInfo::BETA;
                        break;
                    }
                    $i++;
                }

                UpdateChecker::getInstance()->setData(["outdated" => $outdated, "newest_version" => $result]);

                if ($outdated) {
                    if (Language::current() === Language::GERMAN()) {
                        CloudLogger::get()->warn("§cDeine Version von §bPocket§3Cloud §cist nicht aktuell! Bitte installiere die neue Version von §8'§bhttps://github.com/PocketCloudSystem/PocketCloud/releases/latest§8'§c!");
                        CloudLogger::get()->warn("§cDeine Version: §e" . VersionInfo::VERSION . " §8| §cNeuste Version: §e" . $result);
                        CloudLogger::get()->warn("§cStelle außerdem Sicher, dass deine Plugins aktuell sind!");
                    } else {
                        CloudLogger::get()->warn("§cYour version of §bPocket§3Cloud §cis outdated! Please install the newest version from §8'§bgithub.com/PocketCloudSystem/PocketCloud/releases/latest§8'§c!");
                        CloudLogger::get()->warn("§cYour Version: §e" . VersionInfo::VERSION . " §8| §cLatest Version: §e" . $result);
                        CloudLogger::get()->warn("§cAlso make sure that the plugins are up to date!");
                    }
                } else {
                    if ($highVersion) {
                        if (Language::current() === Language::GERMAN()) {
                            CloudLogger::get()->warn("§cDeine Version von §bPocket§3Cloud §cist zu HOCH! Bitte installiere die neue Version von §8'§bhttps://github.com/PocketCloudSystem/PocketCloud/releases/latest§8'§c!");
                            CloudLogger::get()->warn("§cDeine Version: §e" . VersionInfo::VERSION . " §8| §cNeuste Version: §e" . $result);
                            CloudLogger::get()->warn("§cStelle außerdem Sicher, dass deine Plugins aktuell sind!");
                        } else {
                            CloudLogger::get()->warn("§cYour version of §bPocket§3Cloud §cis too HIGH! Please install the latest version from §8'§bgithub.com/PocketCloudSystem/PocketCloud/releases/latest§8'§c!");
                            CloudLogger::get()->warn("§cYour Version: §e" . VersionInfo::VERSION . " §8| §cLatest Version: §e" . $result);
                            CloudLogger::get()->warn("§cAlso make sure that the plugins are up to date!");
                        }
                    } else {
                        if (Language::current() === Language::GERMAN()) {
                            CloudLogger::get()->info("§aDeine Version von §bPocket§3Cloud §aist aktuell!");
                        } else {
                            CloudLogger::get()->info("§aYour version of §bPocket§3Cloud §ais up to date!");
                        }
                    }
                }
            }
        });
    }

    public function isOutdated(): ?bool {
        return $this->data["outdated"] ?? null;
    }

    public function isUpToDate(): bool {
        return !$this->isOutdated();
    }

    public function getNewestVersion(): ?string {
        return $this->data["newest_version"] ?? null;
    }

    public function getCurrentVersion(): string {
        return VersionInfo::VERSION;
    }

    public function setData(array $data): void {
        $this->data = $data;
    }

    public function getData(): array {
        return $this->data;
    }

    public static function getInstance(): self {
        return self::$instance ??= new self;
    }
}