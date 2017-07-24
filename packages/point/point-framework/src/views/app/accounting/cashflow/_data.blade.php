<table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><h3>OPERATIONS</h3></td>
                            <td></td>
                        </tr>

                        @foreach($list_coa_operations as $operations)
                            <?php
                                $operations_value = \JournalHelper::coaValue($operations->id, $date_from, $date_to);
                                $cashflow_operations += $operations_value;
                            ?>
                            <tr>
                                <td><b>{{$operations->account}}</b></td>
                                <td class="text-right">{{number_format_accounting($operations_value)}}</td>
                            </tr>
                        @endforeach

                        <tr>
                            <td><h4>ARUS KAS BERSIH DARI AKTIVITAS OPERASIONAL</h4></td>
                            <td class="text-right">{{number_format_accounting($cashflow_operations)}}</td>
                        </tr>

                        <tr>
                            <td colspan="2"></td>
                        </tr>

                        <tr>
                            <td><h3>INVESTMENT</h3></td>
                            <td></td>
                        </tr>

                        @foreach($list_coa_investment as $investment)
                            <?php
                            $investment_value = \JournalHelper::coaValue($investment->id, $date_from, $date_to);
                            $cashflow_investment += $investment_value;
                            ?>
                            <tr>
                                <td><b>{{$investment->account}}</b></td>
                                <td class="text-right">{{number_format_accounting($investment_value)}}</td>
                            </tr>
                        @endforeach

                        <tr>
                            <td><h4>ARUS KAS BERSIH DARI AKTIVITAS INVESTASI</h4></td>
                            <td class="text-right">{{number_format_accounting($cashflow_investment)}}</td>
                        </tr>

                        <tr>
                            <td colspan="2"></td>
                        </tr>

                        <tr>
                            <td><h3>FINANCING</h3></td>
                            <td></td>
                        </tr>

                        @foreach($list_coa_financing as $financing)
                            <?php
                            $financing_value = \JournalHelper::coaValue($financing->id, $date_from, $date_to);
                            $cashflow_financing += $financing_value;
                            ?>
                            <tr>
                                <td><b>{{$financing->account}}</b></td>
                                <td class="text-right">{{number_format_accounting($financing_value)}}</td>
                            </tr>
                        @endforeach

                        <tr>
                            <td><h4>ARUS KAS BERSIH DARI AKTIVITAS PENDANAAN</h4></td>
                            <td class="text-right">{{number_format_accounting($cashflow_financing)}}</td>
                        </tr>

                        </tbody>
                    </table>
