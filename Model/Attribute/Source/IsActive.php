<?php
namespace SuttonSilver\CMSMenu\Model\Attribute\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class IsActive implements OptionSourceInterface
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' =>self::STATUS_ENABLED,
                'label' => __('Enabled')
            ],
            [
                'value' =>self::STATUS_DISABLED,
                'label' => __('Disabled')
            ]
        ];
    }
}
