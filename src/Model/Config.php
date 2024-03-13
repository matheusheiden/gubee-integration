<?php

declare(strict_types=1);

namespace Gubee\Integration\Model;

use Gubee\Integration\Api\Data\ConfigInterface;
use Gubee\Integration\Api\Enum\MainCategoryEnum;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

use function array_map;
use function explode;

class Config extends AbstractHelper implements ConfigInterface
{
    protected Context $context;
    protected WriterInterface $configWriter;
    protected ReinitableConfigInterface $reinitableConfig;

    public function __construct(
        Context $context,
        WriterInterface $configWriter,
        ReinitableConfigInterface $reinitableConfig
    ) {
        parent::__construct($context);
        $this->reinitableConfig = $reinitableConfig;
        $this->configWriter     = $configWriter;
    }

    /**
     * Set the 'active' system config.
     */
    public function setActive(bool $active): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_ACTIVE, $active);
    }

    /**
     * Get the 'active' system config.
     */
    public function getActive(): bool
    {
        return (bool) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_ACTIVE);
    }

    /**
     * Set the 'api_key' system config.
     */
    public function setApiKey(string $apiKey): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_API_KEY, $apiKey);
    }

    /**
     * Get the 'api_key' system config.
     */
    public function getApiKey(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_API_KEY);
    }

    /**
     * Set the 'api_token' system config.
     *
     * @param mixed $apiToken
     */
    public function setApiToken($apiToken): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_API_TOKEN, $apiToken);
    }

    /**
     * Get the 'api_token' system config.
     */
    public function getApiToken(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_API_TOKEN);
    }

    /**
     * Set the 'api_timeout' system config.
     *
     * @param mixed $apiTimeout
     */
    public function setApiTimeout($apiTimeout): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_API_TIMEOUT, $apiTimeout);
    }

    /**
     * Get the 'api_timeout' system config.
     */
    public function getApiTimeout(): int
    {
        return (int) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_API_TIMEOUT);
    }

    /**
     * Set the 'max_backoff_attempts' system config.
     */
    public function setMaxBackoffAttempts(int $maxBackoffAttempts): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_MAX_BACKOFF_ATTEMPTS, $maxBackoffAttempts);
    }

    /**
     * Get the 'max_backoff_attempts' system config.
     */
    public function getMaxBackoffAttempts(): int
    {
        return (int) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_MAX_BACKOFF_ATTEMPTS);
    }

    /**
     * Set the 'max_attempts' system config.
     */
    public function setMaxAttempts(int $maxAttempts): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_MAX_ATTEMPTS, $maxAttempts);
    }

    /**
     * Get the 'max_attempts' system config.
     */
    public function getMaxAttempts(): int
    {
        return (int) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_MAX_ATTEMPTS);
    }

    /**
     * Set the 'log_level' system config.
     *
     * @param array<string, mixed> $logLevel
     */
    public function setLogLevel(array $logLevel): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_LOG_LEVEL, $logLevel);
    }

    /**
     * Get the 'log_level' system config.
     *
     * @return array<string, mixed>
     */
    public function getLogLevel(): array
    {
        return (array) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_LOG_LEVEL);
    }

    /**
     * Set the 'brand' attribute.
     *
     * @param mixed $brand
     */
    public function setBrandAttribute($brand): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_BRAND, $brand);
    }

    /**
     * Get the 'brand' attribute.
     */
    public function getBrandAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_BRAND);
    }

    /**
     * Set the 'price' attribute.
     *
     * @param mixed $price
     */
    public function setPriceAttribute($price): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_PRICE, $price);
    }

    /**
     * Get the 'price' attribute.
     */
    public function getPrice(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_PRICE);
    }

    /**
     * Set the 'nbm' attribute.
     *
     * @param mixed $nbm
     */
    public function setNbmAttribute($nbm): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_NBM, $nbm);
    }

    /**
     * Get the 'nbm' attribute.
     */
    public function getNbmAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_NBM);
    }

    /**
     * Set the 'ean' attribute.
     *
     * @param mixed $ean
     */
    public function setEanAttribute($ean): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_EAN, $ean);
    }

    /**
     * Get the 'ean' attribute.
     */
    public function getEanAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_EAN);
    }

    /**
     * Set the 'color' attribute.
     *
     * @param mixed $color
     */
    public function setColorAttribute($color): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_COLOR, $color);
    }

    /**
     * Get the 'color' attribute.
     */
    public function getColorAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_COLOR);
    }

    /**
     * Set the 'measure_heading' attribute.
     *
     * @param mixed $measureHeading
     */
    public function setMeasureHeadingAttribute($measureHeading): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_MEASURE_HEADING, $measureHeading);
    }

    /**
     * Get the 'measure_heading' attribute.
     */
    public function getMeasureHeadingAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_MEASURE_HEADING);
    }

    /**
     * Set the 'width' attribute.
     *
     * @param mixed $width
     */
    public function setWidthAttribute($width): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_WIDTH, $width);
    }

    /**
     * Get the 'width' attribute.
     */
    public function getWidthAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_WIDTH);
    }

    /**
     * Set the 'height' attribute.
     *
     * @param mixed $height
     */
    public function setHeightAttribute($height): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_HEIGHT, $height);
    }

    /**
     * Get the 'height' attribute.
     */
    public function getHeightAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_HEIGHT);
    }

    /**
     * Set the 'depth' attribute.
     *
     * @param mixed $depth
     */
    public function setDepthAttribute($depth): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_DEPTH, $depth);
    }

    /**
     * Get the 'depth' attribute.
     */
    public function getDepthAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_DEPTH);
    }

    /**
     * Set the 'measure_unit' attribute.
     *
     * @param mixed $measureUnit
     */
    public function setMeasureUnitAttribute($measureUnit): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_MEASURE_UNIT, $measureUnit);
    }

    /**
     * Get the 'measure_unit' attribute.
     */
    public function getMeasureUnitAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_MEASURE_UNIT);
    }

    /**
     * Set the 'cross_docking_time' attribute.
     *
     * @param mixed $crossDockingTime
     */
    public function setCrossDockingTimeAttribute($crossDockingTime): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_CROSS_DOCKING_TIME, $crossDockingTime);
    }

    /**
     * Get the 'cross_docking_time' attribute.
     */
    public function getCrossDockingTimeAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_CROSS_DOCKING_TIME);
    }

    /**
     * Set the 'warranty_time' attribute.
     *
     * @param mixed $warrantyTime
     */
    public function setWarrantyTimeAttribute($warrantyTime): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_WARRANTY_TIME, $warrantyTime);
    }

    /**
     * Get the 'warranty_time' attribute.
     */
    public function getWarrantyTimeAttribute(): string
    {
        return (string) $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_WARRANTY_TIME);
    }

    /**
     * Set the 'main_category' position.
     */
    public function setMainCategoryPosition(MainCategoryEnum $mainCategory): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_MAIN_CATEGORY, $mainCategory->__toString());
    }

    /**
     * Get the 'main_category' position.
     */
    public function getMainCategoryPosition(): MainCategoryEnum
    {
        return MainCategoryEnum::fromValue(
            $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_MAIN_CATEGORY)
        );
    }

    /**
     * Set the 'blacklist' attribute.
     *
     * @param array<string> $blacklist
     */
    public function setBlacklistAttribute(array $blacklist): self
    {
        return $this->save(ConfigInterface::CONFIG_PATH_BLACKLIST, $blacklist);
    }

    /**
     * Get the 'blacklist' attribute.
     *
     * @return array<string>
     */
    public function getBlacklistAttribute(): array
    {
        $value = $this->scopeConfig->getValue(ConfigInterface::CONFIG_PATH_BLACKLIST);
        $value = explode(',', $value);
        return array_map('trim', $value);
    }

    /**
     * Save the given value to the given path.
     *
     * @param mixed $value
     * @return Config
     */
    protected function save(string $path, $value): self
    {
        $this->configWriter->save($path, $value);
        $this->reinitableConfig->reinit();
        return $this;
    }
}