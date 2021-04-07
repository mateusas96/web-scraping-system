<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Rules\isPasswordValid;

class PasswordTest extends TestCase
{
    /**
     * Valid password requirements:
     * - one uppercase letter
     * - one number
     * - one special character
     */

    /**
     * Testing password only with lowercase letters
     *
     * @return void
     */
    public function test_password_only_with_lowercase_letters()
    {
        $isPasswordValid = new isPasswordValid();

        // response must return false because password does not meet requirements
        $response = $isPasswordValid->passes('password', 'slaptazodis');

        $this->assertEquals($response, false);
    }

    /**
     * Testing password with lowercase letters and one number
     *
     * @return void
     */
    public function test_password_with_lowercase_letters_and_one_number()
    {
        $isPasswordValid = new isPasswordValid();

        // response must return false because password does not meet requirements
        $response = $isPasswordValid->passes('password', 'slaptazodis1');

        $this->assertEquals($response, false);
    }

    /**
     * Testing password with lowercase letters and one uppercase letter
     *
     * @return void
     */
    public function test_password_with_lowercase_letters_and_one_uppercase_letter()
    {
        $isPasswordValid = new isPasswordValid();

        // response must return false because password does not meet requirements
        $response = $isPasswordValid->passes('password', 'Slaptazodis');

        $this->assertEquals($response, false);
    }

    /**
     * Testing password with lowercase letters, one special char and one uppercase letter
     *
     * @return void
     */
    public function test_password_with_lowercase_letters_and_one_special_char_and_one_uppercase_letter()
    {
        $isPasswordValid = new isPasswordValid();

        // response must return false because password does not meet requirements
        $response = $isPasswordValid->passes('password', 'Slaptazodis#');

        $this->assertEquals($response, false);
    }

    /**
     * Testing password with lowercase letters, one special char and one number
     *
     * @return void
     */
    public function test_password_with_lowercase_letters_and_one_special_char_and_one_number()
    {
        $isPasswordValid = new isPasswordValid();

        // response must return false because password does not meet requirements
        $response = $isPasswordValid->passes('password', 'Slaptazodis2');

        $this->assertEquals($response, false);
    }

    /**
     * Testing password with lowercase letters and one special char
     *
     * @return void
     */
    public function test_password_with_lowercase_letters_and_one_special_char()
    {
        $isPasswordValid = new isPasswordValid();

        // response must return false because password does not meet requirements
        $response = $isPasswordValid->passes('password', 'slaptazodis#');

        $this->assertEquals($response, false);
    }

    /**
     * Testing password with lowercase letters, one number and one special char
     *
     * @return void
     */
    public function test_password_with_lowercase_letters_and_one_number_and_one_special_char()
    {
        $isPasswordValid = new isPasswordValid();

        // response must return false because password does not meet requirements
        $response = $isPasswordValid->passes('password', 'slaptazodis1#');

        $this->assertEquals($response, false);
    }

    /**
     * Testing password with lowercase letters, one number, one special char and one uppercase letter
     * In other words - testing valid password
     *
     * @return void
     */
    public function test_password_with_lowercase_letters_and_one_number_and_one_special_char_and_one_uppercase_letter()
    {
        $isPasswordValid = new isPasswordValid();

        // response must return true because password meets requirements
        $response = $isPasswordValid->passes('password', 'Slaptazodis1#');

        $this->assertEquals($response, true);
    }
}
