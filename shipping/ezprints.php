<?php
class ezprints {
    var $ezprints_id, $ezprints_password, $internal_name, $name;
    function ezprints () {
        $this->internal_name = "ezprints";
        $this->name="ezprints";
        $this->is_external=true;
        $this->requires_curl=true;
        $this->needs_zipcode=true;
        return true;
    }

    function getId() {
        return $this->ezprints_id;
    }

    function setId($id) {
        $ezprints_id = $id;
        return true;
    }

    function getName() {
        return $this->name;
    }

    function getInternalName() {
        return $this->internal_name;
    }

    function getForm() {
        $output="<tr>
                    <td>
                        ".TXT_WPSC_EZPRINTS_PARTNER_ID.":
                    </td>
                    <td>
                        <input type='text' name='ezprintsid' value='".get_option("ezprintsid")."'>
                    </td>
                </tr>

                ";
        return $output;
    }

    function submit_form() {
        if ($_POST['ezprintsid'] != '') {
            update_option('ezprintsid', $_POST['ezprintsid']);
        }
        return true;
    }

    function getQuote() {
        global $wpdb, $wpsc_ezprints_quote;

        $body = sendEzprintsShippingRequest($request);
        $xml = simplexml_load_string($body);
        $rates=array();
        foreach ($xml->order->option as $option) {
            $rates[(string) $option['description']." (".(string) $option['type'].")"] = (float) $option['price'];
            error_log(print_r($rates, true));
        }

        $wpsc_ezprints_quote = $rates;
        $ezprintsQuote = $rates;
        $wpsc_ezprints_quote = $rates;
        error_log(">>>SHIPPING_RATES: ".print_r($rates, true));
        return $ezprintsQuote;
    }

    function get_item_shipping() {
    }
}
$ezprints = new ezprints();
$wpsc_shipping_modules[$ezprints->getInternalName()] = $ezprints;
?>