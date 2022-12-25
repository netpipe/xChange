    <script src="assets/tx.js"></script>

<body>
    <table>
        <tr id="svc">
            <td><label for="cmbAPI">Service:</label></td>
            <td><select id="cmbAPI"></select></td>
        </tr>
        <tr class="space">
            <td colspan="2"></td>
        </tr>
        <tr id="wif">
            <td><label for="txtInputs">Input WIFs:</label></td>
            <td><textarea id="txtInputs"></textarea></td>
        </tr>
        <tr id="addr">
            <td><label for="txtOutput">Output:</label></td>
            <td><input type="text" id="txtOutput"></td>
        </tr>
        <tr id="amt">
            <td><label for="txtAmount">Send:</label></td>
            <td><input type="number" id="txtAmount" min="1"> DOGE</td>
        </tr>
        <tr id="chg">
            <td><label for="txtChange">Change:</label></td>
            <td><input type="text" id="txtChange"></td>
        </tr>
        <tr class="button">
            <td colspan="2"><button onclick="ui.assembleTx();">Create Transaction</button></td>
        </tr>
        <tr class="space">
            <td colspan="2"></td>
        </tr>
        <tr id="tx">
            <td colspan="2"><textarea id="txtTX" readonly="readonly"></textarea></td>
        </tr>
        <tr class="button">
            <td colspan="2"><button onclick="ui.sendTx();">Broadcast Transaction</button></td>
        </tr>
        <tr id="link">
            <td colspan="2"><a id="lnkView" target="_blank"></a></td>
        </tr>
    </table>
    <script>
        ui.load();
    </script>
