<?php
// module/Cms/src/Cms/Form/CreateUserForm.php:
namespace Cms\Form;

use Zend\Form\Form;

class SiteSettingsForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('cms');
        $this->setAttribute('method', 'post');
		//$this->setAttribute('enctype','multipart/form-data');
		$this->setAttribute('class', 'form-horizontal');
		$this->setAttribute('name', 'siteSettings');
		$this->setAttribute('id', 'siteSettings');
		//	ID
		$this->add(array(
            'name' => '_id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
		//	FB AppID
		$this->add(array(
            'name' => 'fbappid',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'fbappid',
				'class'	=> 'input-large',
				'maxlength'	=> '200',
            ),
            'options' => array(
            ),
        ));
		//	FB SecretKey
		$this->add(array(
            'name' => 'fbkey',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'fbkey',
				'class'	=> 'input-large',
				'maxlength'	=> '200',
            ),
            'options' => array(
            ),
        ));
		//	FB AppName
		$this->add(array(
            'name' => 'fbapp_name',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'fbapp_name',
				'class'	=> 'input-large',
				'maxlength'	=> '200',
            ),
            'options' => array(
            ),
        ));
		//	FB Page URL
		$this->add(array(
            'name' => 'fb_page',
            'attributes' => array(
                'type'  => 'text',
				'id'	=> 'fb_page',
				'class'	=> 'input-large',
				'maxlength'	=> '255',
				'style' => 'width:440px',
            ),
            'options' => array(
            ),
        ));
		//	Site Timezone
		$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'timezone',
            'options' => array(
                'value_options' => array(
                    "" => "Select Time Zone",
					"-12.00" => "(GMT -12:00) Eniwetok, Kwajalein",
					"-11.00" => "(GMT -11:00) Midway Island, Samoa",
					"-10.00" => "(GMT -10:00) Hawaii",
					"-9.00" => "(GMT -9:00) Alaska",
					"-8.00" => "(GMT -8:00) Pacific Time (US & Canada)",
					"-7.00" => "(GMT -7:00) Mountain Time (US & Canada)",
					"-6.00" => "(GMT -6:00) Central Time (US & Canada), Mexico City",
					"-5.00" => "(GMT -5:00) Eastern Time (US & Canada), Bogota, Lima",
					"-4.00" => "(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz",
					"-3.30" => "(GMT -3:30) Newfoundland",
					"-3.00" => "(GMT -3:00) Brazil, Buenos Aires, Georgetown",
					"-2.00" => "(GMT -2:00) Mid-Atlantic",
					"-1.00" => "(GMT -1:00 hour) Azores, Cape Verde Islands",
					"0.00" => "(GMT) Western Europe Time, London, Lisbon, Casablanca",
					"1.00" => "(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris",
					"2.00" => "(GMT +2:00) Kaliningrad, South Africa",
					"3.00" => "(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg",
					"3.30" => "(GMT +3:30) Tehran",
					"4.00" => "(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi",
					"4.30" => "(GMT +4:30) Kabul",
					"5.00" => "(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent",
					"5.30" => "(GMT +5:30) Bombay, Calcutta, Madras, New Delhi",
					"5.45" => "(GMT +5:45) Kathmandu",
					"6.00" => "(GMT +6:00) Almaty, Dhaka, Colombo",
					"7.00" => "(GMT +7:00) Bangkok, Hanoi, Jakarta",
					"8.00" => "(GMT +8:00) Beijing, Perth, Singapore, Hong Kong",
					"9.00" => "(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk",
					"9.30" => "(GMT +9:30) Adelaide, Darwin",
					"10.00" => "(GMT +10:00) Eastern Australia, Guam, Vladivostok",
					"11.00" => "(GMT +11:00) Magadan, Solomon Islands, New Caledonia",
					"12.00" => "(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka"
                ),
            ),
            'attributes' => array(
                'value' => ''
            )
        ));
		//	Submit
		$this->add(array(
            'name' => 'submit',
            'attributes' => array(
				'id'	=> 'submit_id',
                'type'  => 'submit',
                'value' => 'Save Settings',
				'class'	=> 'btn btn-primary',
            ),
        ));
    }
}
?>