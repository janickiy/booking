<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Helper\Passenger as PassengerHelper;
use ReservationKit\src\Modules\Galileo\Model\Entity\Passenger as GalileoPassenger;

class BookingTraveler extends XmlElement
{
    /**
     * @param \RK_Avia_Entity_Passenger|GalileoPassenger $passenger
     * @param array|null $key
     * @param bool $isNeedMiddleName
     */
    public function __construct(\RK_Avia_Entity_Passenger $passenger, $key, $parameters = array())
    {
        $attributesBookingTraveler = array(
            'Key'          => $passenger->getKey(),             // createBase64UUID(),
            'TravelerType' => $passenger->getType(),            // 'ADT'
            'DOB'          => $passenger->getBorndate('Y-m-d'), // '1969-09-17'
            'Gender'       => $passenger->getGender(),          // 'M'
        );

        $attributesBookingTravelerName = array(
            'First'  => $passenger->getFirstname(),             // 'Jack',
            'Last'   => $passenger->getLastname(),              // 'Smith',
            //'Prefix' => $passenger->getPrefixName()             // 'Mr'
        );

        // TODO
        if (!$parameters['need_middlename']) $attributesBookingTravelerName['Prefix'] = $passenger->getPrefixName();

        $SSRMiddle = '';
        if ($parameters['need_middlename'] && $passenger->getMiddlename()) {
            $attributesBookingTravelerName['Middle'] = $passenger->getMiddlename();
            $SSRMiddle = $passenger->getMiddlename();
        }

        $attributesPhoneNumber = array(
            'Number' => $passenger->getPhone(), //'3333333'
            'Type'   => 'Mobile'
        );

        $attributesEmail = array(
            'EmailID' => empty($passenger->getEmail()) ? 'it@trivago.ru' : $passenger->getEmail(),
            'Type' => 'Home'
        );

        $attributesSSR_DOCS = array(
            'Type'       => 'DOCS',
            'FreeText'   => PassengerHelper::getSSR($passenger, 'DOCS').$SSRMiddle, // 'P/US/F1234567/US/17Sep69/M/24Sep15/Smith/Jack',
            'Status'     => 'HK',
            //'Carrier' => 'QF',
            //'SegmentRef' => createBase64UUID(),
        );

        if ($passenger->isAgeRequired()) {
            $attributesBookingTraveler['Age'] = str_pad($passenger->getAge(), 2, '0', STR_PAD_LEFT);;
        }

        // Мильные карты
        $LoyaltyCardList = null;
        if ($passenger->getLoyaltyCard()) {
            $LoyaltyCardList = array();
            foreach ($passenger->getLoyaltyCard() as $supplierCode => $cardNumber) {
                $attributesLoyaltyCard = array(
                    'CardNumber' => $cardNumber,
                    'SupplierCode' => $supplierCode,
                );

                $LoyaltyCardList[] = new XmlElement('LoyaltyCard', $attributesLoyaltyCard, null, 'com');
            }
        }

        $contentBookingTraveler = array(
            new XmlElement('BookingTravelerName', $attributesBookingTravelerName, null, 'com'),

            /* Для визы
            new XmlElement('DeliveryInfo', array(), array(
                new XmlElement('ShippingAddress', array(), array(
                    new XmlElement('AddressName', array(),'Smiths', 'com'),
                    new XmlElement('Street', array(), '2914 N. Dakota Avenue', 'com'),
                    new XmlElement('City', array(), 'Denver', 'com'),
                    new XmlElement('State', array(), 'CO', 'com'),
                    new XmlElement('PostalCode', array(), '80206', 'com'),
                    new XmlElement('Country', array(), 'US', 'com')
                ), 'com')
            ), 'com'),
            */

            new XmlElement('PhoneNumber', $attributesPhoneNumber, null, 'com'),
            new XmlElement('Email', $attributesEmail, null, 'com'),
            new XmlElement(null, array(), $LoyaltyCardList),
            new XmlElement('SSR', $attributesSSR_DOCS, null, 'com'),

            /* Для визы
            new XmlElement('Address', array(), array(
                new XmlElement('AddressName', array(), 'Smiths', 'com'),
                new XmlElement('Street', array(), '2914 N. Dakota Avenue', 'com'),
                new XmlElement('City', array(), 'Denver', 'com'),
                new XmlElement('State', array(), 'CO', 'com'),
                new XmlElement('PostalCode', array(), '80206', 'com'),
                new XmlElement('Country', array(), 'US', 'com')
            ), 'com'),
            */
        );
		
		// добавляем FOID
		for ($i=0;$i<count($parameters['foid']);$i++)
			$contentBookingTraveler[] = new XmlElement('SSR', array('Type' => 'FOID', 'Carrier' => $parameters['foid'][$i]['airline'],
			/*'Key' => '1002T', */
			//'FreeText' => 'PP'.$passenger->getDocCountry().toTranslit($passenger->getDocNumber().'-'.$parameters['foid'][$i]['segment']
				//.$passenger->getLastname().'/'.$passenger->getFirstname())), null, 'com');
			'FreeText' => 'PP'.toTranslit($passenger->getDocNumber()/*.'-'.$parameters['foid'][$i]['segment']
				.$passenger->getLastname().'/'.$passenger->getFirstname()*/)), null, 'com');			
			
        // Ремарка для ребенка
        if ($passenger->getType() === 'CHD') {
            // P-C03 DOB15JAN14
            $remarkDataText = 'P-C' . $attributesBookingTraveler['Age'] . ' DOB' . strtoupper($passenger->getBorndate('dMy'));

            $contentBookingTraveler[] = new XmlElement('NameRemark', array(),
                new XmlElement('RemarkData', array(),
                    $remarkDataText,
                'com'),
            'com');
        }

        // Особый формат контактных данных пассажира для а/к TK
        if (isset($parameters['airline']) && $parameters['airline'] === 'TK') {
            $phone = $passenger->getPhone();
            if (strlen(trim($phone, '+')) == 11) {
                $phone = '7' . substr($phone, 1);
            }

            $attributesSSR_CTCM = array(
                'Type'      => 'CTCM',
                'FreeText'  => $phone . '/RU',
                'Status'    => 'HK',
                'Carrier'   => 'TK',
                //'SegmentRef' => createBase64UUID(),
            );

            $contentBookingTraveler[] = new XmlElement('SSR', $attributesSSR_CTCM, null, 'com');
        }

        $BookingTraveler = new XmlElement('BookingTraveler', $attributesBookingTraveler, $contentBookingTraveler, 'com');

        parent::__construct(null, array(), $BookingTraveler, 'air');
    }
}