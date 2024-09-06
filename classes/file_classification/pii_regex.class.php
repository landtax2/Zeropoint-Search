<?PHP
class PIIRegex
{
    public static function contains_ssn_hard($string)
    {
        // Regular expression patterns for detecting SSNs
        $patterns = [
            '/\b\d{3}-\d{2}-\d{4}\b/',         // Matches SSNs with dashes, e.g. 123-44-9999
            '/\b(social security number)\b/i',   // Matches the phrase "Social Security Number" in any case
            '/\b(ssn)\b/i'   // Matches the phrase "SSN" in any case
        ];

        // Check if any of the patterns match the string
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $string)) {
                return true;  // SSN found
            }
        }

        return false;  // No SSN found
    }

    public static function contains_ssn_soft($string)
    {
        // Regular expression patterns for detecting SSNs
        $patterns = [
            '/\b\d{3}-\d{2}-\d{4}\b/',         // Matches SSNs with dashes, e.g. 123-44-9999
            '/\b\d{9}\b/',                      // Matches 9-digit SSNs without dashes, e.g. 123449999
            '/\b(SSN|Social Security Number)\s*\d{3}-?\d{2}-?\d{4}\b/i', // Matches "SSN" or "Social Security Number" followed by a SSN format
            '/\b(social security number)\b/i',   // Matches the phrase "Social Security Number" in any case
            '/\b(ssn)\b/i'   // Matches the phrase "Social Security Number" in any case
        ];

        // Check if any of the patterns match the string
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $string)) {
                return true;  // SSN found
            }
        }

        return false;  // No SSN found
    }

    public static function contains_phone_number($string)
    {
        // Regular expression patterns for detecting SSNs
        $patterns = [
            // Pattern for 10-digit phone numbers (e.g., 1234567890)
            '/\b\d{10}\b/',

            // Pattern for phone numbers with dashes (e.g., 123-456-7890)
            '/\b\d{3}[-]\d{3}[-]\d{4}\b/',

            // Pattern for phone numbers with spaces (e.g., 123 456 7890)
            '/\b\d{3}\s\d{3}\s\d{4}\b/',

            // Pattern for phone numbers with parentheses and spaces (e.g., (123) 456-7890)
            '/\(\d{3}\)\s?\d{3}[-]\d{4}/',

            // Pattern for phone numbers with a country code (e.g., +1 123-456-7890)
            '/\+?\d{1,2}\s?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/',

            // Matches the phrase "Social Security Number" in any case
            '/\b(phone number)\b/i',
        ];

        // Check if any of the patterns match the string
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $string)) {
                return true;  // SSN found
            }
        }

        return false;  // No SSN found
    }

    public static function contains_email($string)
    {
        // Regular expression for matching an email address
        $pattern = '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}\b/i';

        // Check if the pattern matches any part of the string
        if (preg_match($pattern, $string)) {
            return true;
        } else {
            return false;
        }
    }

    public static function contains_credit_card($input)
    {
        // Regular expression patterns for different card types
        $patterns = [
            'visa' => '/4[0-9]{12}(?:[0-9]{3})?/',                  // Visa: 13 or 16 digits, starts with 4
            'mastercard' => '/5[1-5][0-9]{14}/',                     // MasterCard: 16 digits, starts with 51-55
            'amex' => '/3[47][0-9]{13}/',                            // American Express: 15 digits, starts with 34 or 37
            'discover' => '/6(?:011|5[0-9]{2})[0-9]{12}/',           // Discover: 16 digits, starts with 6011 or 65
            'diners_club' => '/3(?:0[0-5]|[68][0-9])[0-9]{11}/',     // Diners Club: 14 digits, starts with 300-305, 36 or 38
            'jcb' => '/(?:2131|1800|35\d{3})\d{11}/'                 // JCB: 15 or 16 digits, starts with 2131, 1800, or 35
        ];

        // Remove any non-numeric characters (like spaces or dashes)
        $sanitizedInput = preg_replace('/\D/', '', $input);

        // Check if the sanitized input matches any credit card pattern
        foreach ($patterns as $cardType => $pattern) {
            if (preg_match($pattern, $sanitizedInput)) {
                return true; // Credit card number detected
            }
        }

        return false; // No credit card number found
    }

    public static function contains_password($string)
    {
        // Regular expression patterns for detecting SSNs
        $patterns = [
            '/\b(password)\b/i',   // Matches the phrase "password" in any case
            '/\b(passwords)\b/i',   // Matches the phrase "passwords" in any case
            '/\b(passwd)\b/i',   // Matches the phrase "pass" in any case
        ];

        // Check if any of the patterns match the string
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $string)) {
                return true;  // SSN found
            }
        }

        return false;  // No SSN found
    }

    public static function contains_check($string)
    {
        // Regular expression patterns for detecting SSNs
        $patterns = [
            '/\b(pay to )\b/i',   // Matches the phrase "pay to" in any case
            '/\b(order of)\b/i',   // Matches the phrase "order of" in any case
        ];

        // Check if any of the patterns match the string
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $string)) {
                return true;  // SSN found
            }
        }

        return false;  // No SSN found
    }
}
