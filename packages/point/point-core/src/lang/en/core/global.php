<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the layout views. Feel free to tweak each of these messages here.
    |
    */
    
    'auth'  => [
        'login'     => 'USER :user_name Login
                        <table class="table table-bordered table-condensed">
                            <tr>
                                <td width="200px">IP Address</td>
                                <td>:user_ip</td>
                            </tr>
                            <tr>
                                <td width="200px">Device</td>
                                <td>:user_device</td>
                            </tr>
                            <tr>
                                <td width="200px">OS</td>
                                <td>:user_platform :user_platform_version</td>
                            </tr>
                            <tr>
                                <td width="200px">Browser</td>
                                <td>:user_browser :user_browser_version</td>
                            </tr>
                        </table>',
        'logout'     => 'USER :user_name Logout
                        <table class="table table-bordered table-condensed">
                            <tr>
                                <td width="200px">IP Address</td>
                                <td>:user_ip</td>
                            </tr>
                            <tr>
                                <td width="200px">Device</td>
                                <td>:user_device</td>
                            </tr>
                            <tr>
                                <td width="200px">OS</td>
                                <td>:user_platform :user_platform_version</td>
                            </tr>
                            <tr>
                                <td width="200px">Browser</td>
                                <td>:user_browser :user_browser_version</td>
                            </tr>
                        </table>'
    ],

];
