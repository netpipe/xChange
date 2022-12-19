let ui = {
    load: function () {
        let apiOpts = '';
        for (const [id, api] of Object.entries(DOGE.services)) {
            apiOpts += '<option value="' + id + '" title="' + id + '">' + api.name + '</option>'
        }
        document.getElementById('cmbAPI').innerHTML = apiOpts;
    },
    assembleTx: async function () {
        let apiID = document.getElementById('cmbAPI').value;
        let fromList = [];
        let toList = [];
        fromList = document.getElementById('txtInputs').value.split('\n');
        let sOutput = document.getElementById('txtOutput').value;
        let sAmount = document.getElementById('txtAmount').value;
        toList.push([sOutput, sAmount]);
        let sChange = document.getElementById('txtChange').value;
        toList.push([sChange, -1]);
        let txData = await DOGE.buildTX(apiID, fromList, toList);
        if (txData.substring(0, 1) === '!')
            alert('Error Building Transaction:\n' + txData.substring(1));
        else
            document.getElementById('txtTX').value = txData;
    },
    sendTx: async function () {
        document.getElementById('lnkView').href = '';
        document.getElementById('lnkView').innerHTML = '';
        let apiID = document.getElementById('cmbAPI').value;
        let txData = document.getElementById('txtTX').value;
        if (txData === '') {
            alert('Please build a transaction first');
            return;
        }
        if (confirm('Do you wish to broadcast this transaction?') === false)
            return;
        let txRet = await DOGE.sendTX(apiID, txData);
        if (txRet === false)
            alert('There was an error sending your transaction.');
        else
            alert('Your transaction has been broadcast. The Transaction ID is:\n' + txRet);

        document.getElementById('lnkView').href = DOGE.services[apiID].url.view.replace(/%TXID%/g, txRet);
        document.getElementById('lnkView').innerHTML = 'View Transaction ' + txRet;
    }
};

let DOGE = {
    services: {
        'chain.so': {
            'name': 'SoChain',
            'url': {
                'addr': 'https://chain.so/api/v2/get_tx_unspent/DOGE/%ADDR%',
                'tx': 'https://chain.so/api/v2/get_tx_outputs/DOGE/%TXID%',
                'send': 'https://chain.so/api/v2/send_tx/DOGE',
                'view': 'https://chain.so/tx/DOGE/%TXID%'
            },
            'function': 'getCSTxs',
            'send': 'sendCSTx'
        },
        'blockcypher.com': {
            'name': 'BlockCypher',
            'url': {
                'addr': 'https://api.blockcypher.com/v1/doge/main/addrs/%ADDR%',
                'tx': 'https://api.blockcypher.com/v1/doge/main/txs/%TXID%',
                'send': 'https://api.blockcypher.com/v1/doge/main/txs/push?token=%KEY%',
                'view': 'https://live.blockcypher.com/doge/tx/%TXID%/'
            },
            'function': 'getBCTxs',
            'send': 'sendBCTx'
        },
        'block.io': {
            'name': 'DogeChain',
            'url': {
                'addr': 'https://dogechain.info/api/v1/unspent/%ADDR%',
                'tx': 'https://block.io/api/v2/get_raw_transaction/?api_key=%KEY%&txid=%TXID%',
                'send': 'https://dogechain.info/api/v1/pushtx',
                'view': 'https://dogechain.info/tx/%TXID%'
            },
            'function': 'getDCTxs',
            'send': 'sendDCTx'
        },
        'dogeblocks.com': {
            'name': 'Dogesight',
            'url': {
                'addr': 'https://dogeblocks.com/api/addr/%ADDR%',
                'tx': 'https://dogeblocks.com/api/tx/%TXID%',
                'send': 'https://dogeblocks.com/api/tx/send',
                'view': 'https://dogeblocks.com/tx/%TXID%'
            },
            'function': 'getDBTxs',
            'send': 'sendDBTx'
        }
    },
    buildTX: async function (apiID, fromList, toList) {
        var fnName = 'getCSTxs';
        for (const [id, api] of Object.entries(DOGE.services)) {
            if (id === apiID) {
                fnName = api.function;
                break;
            }
        }
        let txList = [];
        for (let i = 0; i < fromList.length; i++) {
            let sWIF = fromList[i];
            let sAddr = await DOGE.getPubAddr(sWIF);
            console.log(sAddr);
            if (sAddr === false)
                return '!' + sWIF + ' could not be decoded';
            let bAddr = await DOGE.base58D_Check(sAddr, 0x1E);
            if (bAddr === -1 || bAddr === -2 || bAddr === -3 || bAddr === -4)
                return '!' + sAddr + ' could not be decoded: ' + bAddr;
            let getTxs = DOGE[fnName];
            let txs = [];
            txs = await getTxs(sAddr);
            if (txs.length < 1)
                continue;
            for (let a = 0; a < txs.length; a++) {
                txs[a]['pub'] = bAddr;
                txs[a]['priv'] = sWIF;
            }
            txList = txList.concat(txs);
        }
        let totalAmt = 0;
        function Comparator(a, b) {
            if (a['amt'] < b['amt']) return -1;
            if (a['amt'] > b['amt']) return 1;
            return 0;
        }
        txList = txList.sort(Comparator);
        console.log(txList);
        for (let i = 0; i < txList.length; i++) {
            totalAmt += txList[i].amt;
        }
        let amt = 0;
        for (let i = 0; i < toList.length; i++) {
            let iAmt = toList[i][1];
            if (iAmt > -1)
                amt += iAmt;
        }
        if (totalAmt < amt)
            return '!Attempting to send ' + amt + 'DOGE with only ' + totalAmt + ' in all accounts';
        let activeList = [];
        let findAmt = amt;
        totalAmt = 0;
        for (let i = 0; i < txList.length; i++) {
            activeList.push(txList[i]);
            totalAmt += txList[i].amt;
            findAmt -= txList[i].amt;
            if (findAmt <= 0)
                break;
        }
        let changeAmt = totalAmt - amt -1;
        //let changeAmt = 100000000;
        console.log(changeAmt);
        let outList = [];
        for (let i = 0; i < toList.length; i++) {
            let sAddr = toList[i][0];
            let iAmt = toList[i][1];
            console.log(sAddr);
            console.log(iAmt);

            let bAddr = await DOGE.base58D_Check(sAddr, 0x1E);
            if (bAddr === -1 || bAddr === -2 || bAddr === -3 || bAddr === -4) {
                bAddr = await DOGE.base58D_Check(sAddr, 0x16);
                if (bAddr === -1 || bAddr === -2 || bAddr === -3 || bAddr === -4)
                    return '!' + sAddr + ' could not be decoded: ' + bAddr;
                let toData = [];
                toData['type'] = 'p2sh';
                toData['addr'] = bAddr;
                if (iAmt > -1)
                    toData['amt'] = iAmt;
                else
                    toData['amt'] = changeAmt;
                if (toData['amt'] <= 0)
                    continue;
                outList.push(toData);
            }
            else {
                let toData = [];
                toData['type'] = 'p2pkh';
                toData['addr'] = bAddr;
                console.log(iAmt);
                if (iAmt > -1){
                    toData['amt'] = iAmt;
                    console.log("true");
                }
                else{
                    console.log("true");
                    toData['amt'] = changeAmt;
                }
                if (toData['amt'] <= 0){
                    console.log("true");
                    continue;
                }
                outList.push(toData);
            }
        }
        for (let i = 0; i < activeList.length; i++) {
            let toSign = DOGE.makeTxToSign(activeList, i, outList);
            let hSign = await DOGE.doubleHash_SHA(toSign);
            hSign = BigInt('0x' + tools.arrayBufferToHexString(hSign, true));
            activeList[i].sig = await DOGE.signScript(hSign, activeList[i].priv);
        }
        let tx = DOGE.makeTx(activeList, outList);
        return tx;
    },
    sendTX: async function (apiID, txData) {
        var fnName = 'sendCSTx';
        for (const [id, api] of Object.entries(DOGE.services)) {
            if (id === apiID) {
                fnName = api.send;
                break;
            }
        }
        let sendTx = DOGE[fnName];
        return sendTx(txData);
    },
    getCSTxs: async function (addr) {
        let mySvc = DOGE.services['chain.so'];
        let sURL = mySvc.url.addr.replace(/%ADDR%/g, addr);
        let jRet = await tools.getMsgTo(sURL).catch((err) => {
            console.log('Error getting TX List: ', err);
        });
        if (jRet === undefined)
            return false;
        let txList = [];
        if (jRet.status !== 'success')
            return false;
        if (jRet.data === undefined)
            return false;
        if (jRet.data.txs === undefined)
            return false;
        if (jRet.data.txs.length > 0) {
            for (let i = 0; i < jRet.data.txs.length; i++) {
                if (jRet.data.txs[i].output_no === undefined)
                    continue;
                if (jRet.data.txs[i].output_no < 0)
                    continue;
                if (jRet.data.txs[i].txid === undefined)
                    continue;
                if (jRet.data.txs[i].value === undefined)
                    continue;
                if (jRet.data.txs[i].confirmations === undefined)
                    continue;
                if (parseInt(jRet.data.txs[i].confirmations) < 3)
                    continue;
                let txData = [];
                let tx = jRet.data.txs[i].txid;
                txData['tx'] = tx;
                txData['idx'] = jRet.data.txs[i].output_no;
                txData['amt'] = parseFloat(jRet.data.txs[i].value);
                txList.push(txData);
            }
        }
        return txList;
    },
    sendCSTx: async function (txData) {
        let mySvc = DOGE.services['chain.so'];
        let sURL = mySvc.url.send;
        let sParams = 'tx_hex=' + txData;
        let jRet = await tools.postMsgTo(sURL, sParams, 'application/x-www-form-urlencoded').catch((err) => {
            console.log('Error sending TX to chain.so: ', err);
        });
        if (jRet === undefined)
            return false;
        if (jRet.data === undefined)
            return false;
        if (jRet.data.txid !== undefined)
            return jRet.data.txid;
        console.log(jRet);
        return false;
    },
    getBCTxs: async function (addr) {
        let mySvc = DOGE.services['blockcypher.com'];
        let sURL = mySvc.url.addr.replace(/%ADDR%/g, addr);
        let jRet = await tools.getMsgTo(sURL).catch((err) => {
            console.log('Error getting TX List: ', err);
        });
        if (jRet === undefined)
            return false;
        let txList = [];
        if (jRet.txrefs === undefined)
            return false;
        if (jRet.txrefs.length > 0) {
            for (let i = 0; i < jRet.txrefs.length; i++) {
                if (jRet.txrefs[i].spent)
                    continue;
                if (jRet.txrefs[i].tx_output_n === undefined)
                    continue;
                if (jRet.txrefs[i].tx_output_n < 0)
                    continue;
                if (jRet.txrefs[i].tx_hash === undefined)
                    continue;
                if (jRet.txrefs[i].value === undefined)
                    continue;
                if (jRet.txrefs[i].confirmations === undefined)
                    continue;
                if (parseInt(jRet.txrefs[i].confirmations) < 3)
                    continue;
                let txData = {};
                let tx = jRet.txrefs[i].tx_hash;
                txData['tx'] = tx;
                txData['idx'] = jRet.txrefs[i].tx_output_n;
                txData['amt'] = (jRet.txrefs[i].value / 100000000);
                txList.push(txData);
            }
        }
        return txList;
    },
    sendBCTx: async function (txData) {
        let myKey = 'GET_YOUR_OWN_KEY_FROM_https://accounts.blockcypher.com/signup';
        let mySvc = DOGE.services['blockcypher.com'];
        let sURL = mySvc.url.send.replace(/%KEY%/g, myKey);
        let sParams = '{"tx": "' + txData + '"}';
        let jRet = await tools.postMsgTo(sURL, sParams, 'application/json').catch((err) => {
            console.log('Error sending TX to blockcypher: ', err);
        });
        if (jRet === undefined)
            return false;
        if (jRet.tx === undefined)
            return false;
        if (jRet.tx.hash !== undefined)
            return jRet.tx.hash;
        console.log(jRet);
        return false;
    },
    getDCTxs: async function (addr) {
        let mySvc = DOGE.services['block.io'];
        let sURL = mySvc.url.addr.replace(/%ADDR%/g, addr);
        let jRet = await tools.getMsgTo(sURL).catch((err) => {
            console.log('Error getting TX List: ', err);
        });
        if (jRet === undefined)
            return false;
        let txList = [];
        if (jRet.success !== 1)
            return false;
        if (jRet.unspent_outputs === undefined)
            return false;
        if (jRet.unspent_outputs.length > 0) {
            for (let i = 0; i < jRet.unspent_outputs.length; i++) {
                if (jRet.unspent_outputs[i].tx_output_n === undefined)
                    continue;
                if (jRet.unspent_outputs[i].tx_output_n < 0)
                    continue;
                if (jRet.unspent_outputs[i].tx_hash === undefined)
                    continue;
                if (jRet.unspent_outputs[i].value === undefined)
                    continue;
                if (jRet.unspent_outputs[i].confirmations === undefined)
                    continue;
                if (parseInt(jRet.unspent_outputs[i].confirmations) < 3)
                    continue;
                let txData = [];
                let tx = jRet.unspent_outputs[i].tx_hash;
                txData['tx'] = tx;
                txData['idx'] = jRet.unspent_outputs[i].tx_output_n;
                txData['amt'] = (parseInt(jRet.unspent_outputs[i].value) / 100000000);
                txList.push(txData);
            }
        }
        return txList;
    },
    sendDCTx: async function (txData) {
        let mySvc = DOGE.services['block.io'];
        let sURL = mySvc.url.send;
        let sParams = 'tx=' + txData;
        let jRet = await tools.postMsgTo(sURL, sParams, 'application/x-www-form-urlencoded').catch((err) => {
            console.log('Error sending TX to dogechain: ', err);
        });
        if (jRet === undefined)
            return false;
        if (jRet.success !== 1)
            return false;
        if (jRet.tx_hash !== undefined)
            return jRet.tx_hash;
        console.log(jRet);
        return false;
    },
    getDBTxs: async function (addr) {
        let mySvc = DOGE.services['dogeblocks.com'];
        let sURL = mySvc.url.addr.replace(/%ADDR%/g, addr);
        let jRet = await tools.getMsgTo(sURL).catch((err) => {
            console.log('Error getting TX List: ', err);
        });
        if (jRet === undefined)
            return false;
        let txList = [];
        if (jRet.transactions === undefined)
            return false;
        if (jRet.transactions.length > 0) {
            for (let i = 0; i < jRet.transactions.length; i++) {
                await tools.sleep(200);
                let tx = jRet.transactions[i];
                let sTxURL = mySvc.url.tx.replace(/%TXID%/g, tx);
                let jRetTx = await tools.getMsgTo(sTxURL).catch((err) => {
                    console.log('Error getting TX ' + tx + ' data: ', err);
                });
                if (jRetTx === undefined)
                    continue;
                if (jRetTx.vout === undefined)
                    continue;
                if (jRetTx.vout.length < 1)
                    continue;
                if (jRetTx.confirmations === undefined)
                    continue;
                if (parseInt(jRetTx.confirmations) < 2)
                    continue;
                for (let o = 0; o < jRetTx.vout.length; o++) {
                    if (jRetTx.vout[o].spentTxId !== undefined)
                        continue;
                    if (jRetTx.vout[o].scriptPubKey === undefined)
                        continue;
                    if (jRetTx.vout[o].scriptPubKey.addresses === undefined)
                        continue;
                    if (jRetTx.vout[o].scriptPubKey.addresses.length < 1)
                        continue;
                    for (let a = 0; a < jRetTx.vout[o].scriptPubKey.addresses.length; a++) {
                        if (jRetTx.vout[o].scriptPubKey.addresses[a] === addr) {
                            let txData = {};
                            txData['tx'] = tx;
                            txData['idx'] = jRetTx.vout[o].n;
                            txData['amt'] = parseFloat(jRetTx.vout[o].value);
                            txList.push(txData);
                        }
                    }
                }
            }
        }
        return txList;
    },
    sendDBTx: async function (txData) {
        let mySvc = DOGE.services['dogeblocks.com'];
        let sURL = mySvc.url.send;
        let sParams = 'rawtx=' + txData;
        let jRet = await tools.postMsgTo(sURL, sParams, 'application/x-www-form-urlencoded').catch((err) => {
            console.log('Error sending TX to dogeblocks: ', err);
        });
        if (jRet === undefined)
            return false;
        if (jRet.txid !== undefined)
            return jRet.txid;
        console.log(jRet);
        return false;
    },
    getPubPt: async function (wif) {
        let key = await DOGE.base58D_Check(wif, 0x9E);
        if (key === -1 || key === -2 || key === -3 || key === -4)
            return false;
        if (key.slice(-1)[0] === 1)
            key = key.slice(0, -1);
        let k1 = new ecc_curve('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F', '00', '07');
        let k1G = new ecc_point(k1, '79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798', '483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8', 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141');
        let n = BigInt('0x' + tools.arrayBufferToHexString(key));
        return ecc_point.mul(n, k1G);
    },
    getPubAddr: async function (wif) {
        let key = await DOGE.base58D_Check(wif, 0x9E);
        if (key === -1 || key === -2 || key === -3 || key === -4)
            return false;
        let compress = false;
        if (key.slice(-1)[0] === 1) {
            key = key.slice(0, -1);
            compress = true;
        }
        let k1 = new ecc_curve('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F', '00', '07');
        let k1G = new ecc_point(k1, '79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798', '483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8', 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141');
        let n = BigInt('0x' + tools.arrayBufferToHexString(key));
        let ptPub = ecc_point.mul(n, k1G);
        if (ptPub === false)
            return false;
        let toHash = '';
        if (compress) {
            if (ptPub.Y % BigInt(2) === BigInt(0))
                toHash = '02';
            else
                toHash = '03';
            let sK = DOGE.padHex(ptPub.X, 32);
            toHash += sK;
        }
        else {
            toHash = '04';
            let sX = DOGE.padHex(ptPub.X, 32);
            toHash += sX;
            let sY = DOGE.padHex(ptPub.Y, 32);
            toHash += sY;
        }
        let bRIPE = await DOGE.doubleHash_RIPE(toHash);
        return await DOGE.base58E_Check(bRIPE, 0x1E);
    },
    makeTxToSign: function (inTxs, inIDX, outList) {
        let tx = '';
        tx += DOGE.padHex(1, 4, true);
        let inCt = inTxs.length.toString(16);
        tx += DOGE.varInt(inTxs.length);
        for (let i = 0; i < inTxs.length; i++) {
            let input = DOGE.swapEndian(inTxs[i].tx);
            input += DOGE.padHex(inTxs[i].idx, 4, true);
            let inSPK = '';
            if (i === inIDX)
                inSPK = '76a9' + DOGE.varInt(inTxs[i].pub.byteLength) + tools.arrayBufferToHexString(inTxs[i].pub.buffer) + '88ac';
            let inLen = 0;
            if (inSPK.length > 0)
                inLen = inSPK.length / 2;
            input += DOGE.varInt(inLen) + inSPK;
            input += 'ffffffff';
            tx += input;
        }
        let outCount = outList.length;
        tx += DOGE.varInt(outCount);
        for (let i = 0; i < outList.length; i++) {
            let output = DOGE.padHex(outList[i].amt * 100000000, 8, true);
            let outSPK = '';
            if (outList[i].type === 'p2pkh')
                outSPK = '76a9' + DOGE.varInt(outList[i].addr.byteLength) + tools.arrayBufferToHexString(outList[i].addr.buffer) + '88ac';
            else
                outSPK = 'a9' + DOGE.varInt(outList[i].addr.byteLength) + tools.arrayBufferToHexString(outList[i].addr.buffer) + '87';
            output += DOGE.varInt(outSPK.length / 2) + outSPK
            tx += output;
        }
        tx += DOGE.padHex(0, 4, true);
        tx += DOGE.padHex(1, 4, true);
        return tx;
    },
    makeTx: function (inTxs, outList) {
        let tx = '';
        tx += DOGE.padHex(1, 4, true);
        let inCt = inTxs.length.toString(16);
        tx += DOGE.varInt(inTxs.length);
        for (let i = 0; i < inTxs.length; i++) {
            let input = DOGE.swapEndian(inTxs[i].tx);
            input += DOGE.padHex(inTxs[i].idx, 4, true);
            input += DOGE.varInt(inTxs[i].sig.length / 2) + inTxs[i].sig;
            input += 'ffffffff';
            tx += input;
        }
        let outCount = outList.length;
        tx += DOGE.varInt(outCount);
        for (let i = 0; i < outList.length; i++) {
            let output = DOGE.padHex(outList[i].amt * 100000000, 8, true);
            let outSPK = '';
            if (outList[i].type === 'p2pkh')
                outSPK = '76a9' + DOGE.varInt(outList[i].addr.byteLength) + tools.arrayBufferToHexString(outList[i].addr.buffer) + '88ac';
            else
                outSPK = 'a9' + DOGE.varInt(outList[i].addr.byteLength) + tools.arrayBufferToHexString(outList[i].addr.buffer) + '87';
            output += DOGE.varInt(outSPK.length / 2) + outSPK
            tx += output;
        }
        tx += DOGE.padHex(0, 4, true);
        return tx;
    },
    signScript: async function (data, priv) {
        let pub = await DOGE.getPubPt(priv);
        if (pub === false)
            return false;
        let sig = '30';
        let bPriv = await DOGE.base58D_Check(priv, 0x9E);
        if (bPriv === -1 || bPriv === -2 || bPriv === -3 || bPriv === -4)
            return false;
        let sPriv = tools.arrayBufferToHexString(bPriv, true);
        let compressed = false;
        if (sPriv.substring(sPriv.length - 2) === '01') {
            sPriv = sPriv.substring(0, sPriv.length - 2);
            compressed = true;
        }
        let sRet = ecc_math.sign(data, BigInt('0x' + sPriv));
        let r = DOGE.padHex(sRet.r, 32);
        if (r.substring(0, 2) === '00') {
            while (r.substring(0, 2) === '00')
                r = r.substring(2);
        }
        if (parseInt(r.substring(0, 2), 16) >= 0x80)
            r = '00' + r;
        let s = DOGE.padHex(sRet.s, 32);
        if (s.substring(0, 2) === '00') {
            while (s.substring(0, 2) === '00')
                s = s.substring(2);
        }
        if (parseInt(s.substring(0, 2), 16) >= 0x80)
            s = '00' + s;
        let sigR = DOGE.padHex(r.length / 2, 1) + r;
        let sigS = DOGE.padHex(s.length / 2, 1) + s;
        let sBlock = '02' + sigR + '02' + sigS;
        sig += DOGE.padHex(sBlock.length / 2, 1) + sBlock;
        let sSig = DOGE.varInt((sig.length / 2) + 1) + sig + '01';
        let sPub = '';
        if (compressed) {
            if (pub.Y % BigInt(2) === BigInt(0))
                sPub = '02';
            else
                sPub = '03';
            let sK = DOGE.padHex(pub.X, 32);
            sPub += sK;
        }
        else {
            sPub = '04' + DOGE.padHex(pub.X, 32) + DOGE.padHex(pub.Y, 32);
        }
        sSig += DOGE.varInt(sPub.length / 2) + sPub;
        return sSig;
    },
    generateK: function () {
        let k = new Uint8Array(32);
        let valid = true;
        let minKey = BigInt(1);
        let maxKey = BigInt('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364140');
        do {
            valid = true;
            window.crypto.getRandomValues(k);
            let bigKey = BigInt('0x' + tools.arrayBufferToHexString(k));
            if (bigKey > maxKey)
                valid = false;
            if (bigKey < minKey)
                valid = false;
        } while (valid === false);
        return k;
    },
    swapEndian: function (str) {
        if (typeof str !== 'string' && !(str instanceof String))
            str = tools.arrayBufferToHexString(str);
        var a = str.match(/../g);
        a.reverse();
        return a.join('');
    },
    padHex: function (val, bytes, little = false) {
        let sVal = val.toString(16);
        if (sVal.length % 2 === 1)
            sVal = '0' + sVal;
        while (sVal.length < bytes * 2)
            sVal = '00' + sVal;
        if (little)
            sVal = DOGE.swapEndian(sVal);
        return sVal;
    },
    varInt: function (i) {
        if (i < 0xfd)
            return DOGE.padHex(i, 1);
        if (i <= 0xFFFF)
            return 'FD' + DOGE.padHex(i, 2, true);
        if (i <= 0xFFFFFFFF)
            return 'FE' + DOGE.padHex(i, 4, true);
        return 'FF' + DOGE.padHex(i, 8, true);
    },
    base58Encode: function (bin) {
        if (typeof bin === 'string' || bin instanceof String)
            bin = tools.hexStringToArrayBuffer(bin);
        let dictionary = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        let n = BigInt('0x' + tools.arrayBufferToHexString(bin));
        let r = '';
        let b58 = BigInt(58);
        while (n >= b58) {
            let div = n / b58;
            let mod = n % b58;
            mod = parseInt(mod);
            r = dictionary.substring(mod, mod + 1) + r;
            n = div;
        }
        if (n !== BigInt(0)) {
            n = parseInt(n);
            r = dictionary.substring(n, n + 1) + r;
        }
        for (let i = 0; i < bin.length; i++) {
            if (bin[i] !== 0)
                break;
            r = dictionary.substring(0, 1) + r;
        }
        return r;
    },
    base58E_Check: async function (bin, id, suffix = -1) {
        if (typeof bin === 'string' || bin instanceof String)
            bin = tools.hexStringToArrayBuffer(bin);
        let sBIN = tools.arrayBufferToHexString(bin);
        sBIN = DOGE.padHex(id, 1) + sBIN;
        if (suffix > -1)
            sBIN += DOGE.padHex(suffix, 1);
        let bSum = await window.crypto.subtle.digest('SHA-256', tools.hexStringToArrayBuffer(sBIN));
        bSum = await window.crypto.subtle.digest('SHA-256', bSum);
        sBIN += tools.arrayBufferToHexString(bSum).substring(0, 8);
        return DOGE.base58Encode(sBIN);
    },
    base58Decode: function (str) {
        let dictionary = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        let decoded = BigInt(0);
        while (str) {
            let alphabetPosition = dictionary.indexOf(str[0]);
            if (alphabetPosition < 0)
                return false;
            let powerOf = BigInt(str.length - 1);
            let newVal = BigInt(alphabetPosition) * (BigInt(58) ** powerOf);
            decoded = decoded + newVal;
            str = str.substring(1);
        }
        let hexStr = decoded.toString(16);
        if (hexStr === '')
            return false;
        for (let i = 0; i < str.length; i++) {
            if (str.substring(i, i + 1) !== dictionary.substring(0, 1))
                break;
            hexStr = '00' + hexStr;
        }
        if (hexStr.length % 2 === 1)
            hexStr = '0' + hexStr;
        return tools.hexStringToArrayBuffer(hexStr);
    },
    base58D_Check: async function (str, expectedID) {
        // -1 = BigInt not supported
        // -2 = not base58
        // -3 = ID is not expectedID
        // -4 = Checksum mismatch

        if (!('BigInt' in window))
            return -1;
        let aData = DOGE.base58Decode(str);
        if (aData === false)
            return -2;
        let bAddr = new Uint8Array(aData);
        let bID = bAddr.slice(0, 1);
        let bSum = bAddr.slice(-4);
        if (bID[0] !== expectedID)
            return -3;
        let kSum = await DOGE.doubleHash_SHA(bAddr.slice(0, -4));
        kSum = kSum.slice(0, 4);
        if (tools.arrayBufferToHexString(kSum) !== tools.arrayBufferToHexString(bSum))
            return -4;
        return bAddr.slice(1, -4);
    },
    doubleHash_SHA: async function (bin) {
        if (typeof bin === 'string' || bin instanceof String)
            bin = tools.hexStringToArrayBuffer(bin);
        let bSum = await window.crypto.subtle.digest('SHA-256', bin);
        return await window.crypto.subtle.digest('SHA-256', bSum);
    },
    doubleHash_RIPE: async function (bin) {
        if (typeof bin === 'string' || bin instanceof String)
            bin = tools.hexStringToArrayBuffer(bin);
        let kSHA = await window.crypto.subtle.digest('SHA-256', bin);
        let byteLen = kSHA.byteLength;
        let arI = new Int32Array(kSHA);
        let arD = [];
        for (let i = 0; i < arI.length; i++) {
            arD.push(arI[i]);
        }
        let ripe = tools.ripemd160(arD, byteLen);
        return new Uint8Array(new Int32Array(ripe).buffer);
    }
};

let ecc_curve = class {
    constructor(prime, a, b) {
        this.a = BigInt('0x' + a);
        this.b = BigInt('0x' + b);
        this.prime = BigInt('0x' + prime);
    }
    get A() {
        return this.a;
    }
    get B() {
        return this.b;
    }
    get Prime() {
        return this.prime;
    }
    contains(x, y) {
        if (this.a === BigInt(0)) {
            if ((((y ** BigInt(2)) - ((x ** BigInt(3)) + this.b)) % this.prime) === BigInt(0))
                return true;
        }
        else {
            if ((((y ** BigInt(2)) - (((x ** BigInt(3)) + (this.a * x)) + this.b)) % this.prime) === BigInt(0))
                return true;
        }
        return false;
    }
    static cmp(curve1, curve2) {
        if (curve1.A === curve2.A && curve1.B === curve2.B && curve1.Prime === curve2.Prime)
            return 0;
        return 1;
    }
};

let ecc_point = class {
    constructor(curve, x, y, order = null) {
        this.curve = curve;
        if (typeof x === 'string' || x instanceof String)
            this.x = BigInt('0x' + x);
        else
            this.x = x;
        if (typeof y === 'string' || y instanceof String)
            this.y = BigInt('0x' + y);
        else
            this.y = y;
        if (order === null)
            this.order = null;
        else
            this.order = BigInt('0x' + order);
        /*
        if (this.curve !== null)
        {
         if (!this.curve.contains(this.x, this.y))
         {
          this.err = 'Curve does not contain point';
          return;
         }
         if (this.order !== null)
         {
          if (this.cmp(this.mul(this.order, this), 'infinity') !== 0)
          {
           this.err = 'Order * Self must not equal Infinity';
           return;
          }
         }
        }
        */
    }
    static cmp(p1, p2) {
        if (p1.X === p2.X && p1.y === p2.Y && ecc_curve.cmp(p1.Curve, p2.Curve) === 0)
            return 0;
        return 1;
    }
    static add(p1, p2) {
        if (ecc_curve.cmp(p1.Curve, p2.Curve) !== 0)
            return false;
        let xComp = 0;
        if (p1.X > p2.X)
            xComp = 1;
        else if (p1.X < p2.X)
            xComp = -1;
        if (BigInt(xComp) % p1.Curve.Prime === BigInt(0)) {
            if (p1.Y + p2.Y === p1.Curve.Prime)
                return 'Infinity';
            return ecc_point.double(p1);
        }
        let p = p1.Curve.Prime;
        let invP = ecc_math.inverse_mod((p2.X - p1.X), p);
        if (invP === false)
            return false;
        let l = ((p2.Y - p1.Y) * invP) % p;
        let x3 = (((l ** BigInt(2)) - p1.X) - p2.X) % p;
        let y3 = ((l * (p1.X - x3)) - p1.Y) % p;
        if (y3 < 0)
            y3 = p + y3;
        let p3 = new ecc_point(p1.Curve, x3, y3);
        return p3;
    }
    static mul(x2, p1) {
        let e = x2;
        if (p1.Order !== null)
            e = e % p1.Order;
        if (e === BigInt(0))
            return 'Infinity';
        if (e < 0)
            return 0;
        let e3 = BigInt(3) * e;
        let neg_self = new ecc_point(p1.Curve, p1.X, (BigInt(0) - p1.Y), p1.Order);
        let i = ecc_point.leftmost_bit(e3) / BigInt(2);
        let result = p1;
        while (i > 1) {
            result = ecc_point.double(result);
            let e3bit = ((e3 & BigInt(i)) === BigInt(0));
            let ebit = ((e & BigInt(i)) === BigInt(0));
            if (!e3bit && ebit)
                result = ecc_point.add(result, p1);
            else if (e3bit && !ebit)
                result = ecc_point.add(result, neg_self);
            i = i / BigInt(2);
        }
        return result;
    }
    static leftmost_bit(x) {
        if (x < 0)
            return 0;
        let result = BigInt(1);
        while (result <= x) {
            result = BigInt(2) * result;
        }
        return result / BigInt(2);
    }
    static double(p1) {
        let big2 = BigInt(2);
        let big3 = BigInt(3);
        let p = p1.Curve.Prime;
        let a = p1.Curve.A;
        let inverse = ecc_math.inverse_mod(big2 * p1.Y, p);
        let three_x2 = big3 * (p1.X ** big2);
        let l = ((three_x2 + a) * inverse) % p;
        let x3 = ((l ** big2) - (big2 * p1.X)) % p;
        let y3 = ((l * (p1.X - x3)) - p1.Y) % p;
        if (y3 < 0)
            y3 = p + y3;
        return new ecc_point(p1.Curve, x3, y3);
    }
    get X() {
        return this.x;
    }
    get Y() {
        return this.y;
    }
    get Curve() {
        return this.curve;
    }
    get Order() {
        return this.order;
    }
};

let ecc_math = {
    sign: function (bin, priv) {
        let random_k = DOGE.generateK();
        let k1 = new ecc_curve('FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F', '00', '07');
        let k1G = new ecc_point(k1, '79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798', '483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8', 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141');
        let n = k1G.Order;
        let k = BigInt('0x' + tools.arrayBufferToHexString(random_k));
        k = k % n;
        let p1 = ecc_point.mul(k, k1G);
        let r = p1.X;
        if (r === BigInt(0))
            return ecc_math.sign(bin, priv);

        let s = (ecc_math.inverse_mod(k, n) * (bin + (priv * r) % n)) % n;
        if (s === BigInt(0))
            return ecc_math.sign(bin, priv);
        if (s >= n / BigInt(2))
            s = n - s;
        let ret = {};
        ret['r'] = r;
        ret['s'] = s;
        return ret;
    },
    inverse_mod: function (a, m) {
        while (a < 0) {
            a = m + a;
        }
        while (m < a) {
            a = a % m;
        }
        let c = a;
        let d = m;
        let uc = BigInt(1);
        let vc = BigInt(0);
        let ud = BigInt(0);
        let vd = BigInt(0);
        while (c !== BigInt(0)) {
            let temp1 = c;
            let q = d / c;
            c = d % c;
            d = temp1;
            let temp2 = uc;
            let temp3 = vc;
            uc = ud - (q * uc);
            vc = vd - (q - vc);
            ud = temp2;
            vd = temp3;
        }
        if (d === BigInt(1)) {
            if (ud === BigInt(0))
                return ud;
            return ud + m;
        }
        return false;
    }
};

let tools = {
    sendPostTo: async function (addr, params, enc) {
        return new Promise((resolve, reject) => {
            let xmlhttp;
            if (window.XMLHttpRequest)
                xmlhttp = new XMLHttpRequest();
            else
                xmlhttp = new window.ActiveXObject('Microsoft.XMLHTTP');
            xmlhttp.onreadystatechange = function () {
                if (xmlhttp.readyState !== 4)
                    return;
                if (xmlhttp.status < 200 || xmlhttp.status > 299) {
                    if (xmlhttp.status === 0)
                        reject('Empty Response');
                    else
                        reject('HTTP Error ' + xmlhttp.status);
                    return;
                }
                if (xmlhttp.responseText === '') {
                    reject('Empty Response');
                    return;
                }
                try {
                    let respData = JSON.parse(xmlhttp.responseText);
                    if (respData.hasOwnProperty('error')) {
                        reject(respData.err);
                        return;
                    }
                    resolve(respData);
                }
                catch (ex) {
                    reject(xmlhttp.responseText);
                    return;
                }
            };
            xmlhttp.onerror = function (err) {
                reject('Connection Error');
            };
            xmlhttp.timeout = 5000;
            xmlhttp.open('POST', addr, true);
            xmlhttp.setRequestHeader('Content-type', enc);
            xmlhttp.send(params);
        });
    },
    postMsgTo: async function (addr, params, enc) {
        return new Promise((resolve, reject) => {
            let attempts = 3;
            const XHR = () => {
                if (attempts > 0) {
                    attempts--;
                    tools.sendPostTo(addr, params, enc).then
                        (
                            (res) => { resolve(res); }
                        ).catch
                        (
                            (e) => {
                                if (e !== 'Empty Response') {
                                    reject(e);
                                    return;
                                }
                                setTimeout(() => { XHR(); }, 2500);
                            }
                        );
                }
            };
            XHR();
        });
    },
    sendGetTo: async function (addr) {
        return new Promise((resolve, reject) => {
            let xmlhttp;
            if (window.XMLHttpRequest)
                xmlhttp = new XMLHttpRequest();
            else
                xmlhttp = new window.ActiveXObject('Microsoft.XMLHTTP');
            xmlhttp.onreadystatechange = function () {
                if (xmlhttp.readyState !== 4)
                    return;
                if (xmlhttp.status < 200 || xmlhttp.status > 299) {
                    if (xmlhttp.status === 0)
                        reject('Empty Response');
                    else
                        reject('HTTP Error ' + xmlhttp.status);
                    return;
                }
                if (xmlhttp.responseText === '') {
                    reject('Empty Response');
                    return;
                }
                try {
                    let respData = JSON.parse(xmlhttp.responseText);
                    if (respData.hasOwnProperty('error')) {
                        reject(respData.err);
                        return;
                    }
                    resolve(respData);
                }
                catch (ex) {
                    reject(xmlhttp.responseText);
                    return;
                }
            };
            xmlhttp.onerror = function (err) {
                reject('Connection Error');
            };
            xmlhttp.timeout = 5000;
            xmlhttp.open('GET', addr, true);
            xmlhttp.send();
        });
    },
    getMsgTo: async function (addr) {
        return new Promise((resolve, reject) => {
            let attempts = 3;
            const XHR = () => {
                if (attempts > 0) {
                    attempts--;
                    tools.sendGetTo(addr).then
                        (
                            (res) => { resolve(res); }
                        ).catch
                        (
                            (e) => {
                                if (e !== 'Empty Response') {
                                    reject(e);
                                    return;
                                }
                                setTimeout(() => { XHR(); }, 2500);
                            }
                        );
                }
            };
            XHR();
        });
    },
    padHex: function (d, unsigned) {
        unsigned = (typeof unsigned !== 'undefined') ? unsigned : false;
        let h = null;
        if (typeof d === 'number') {
            if (unsigned) {
                h = d.toString(16);
                return h.length % 2 ? '000' + h : '00' + h;
            }
            else {
                h = (d).toString(16);
                return h.length % 2 ? '0' + h : h;
            }
        }
        else if (typeof d === 'string') {
            h = (d.length / 2).toString(16);
            return h.length % 2 ? '0' + h : h;
        }
    },
    arrayBufferToHexString: function (arrayBuffer, signedHex = false) {
        let byteArray = new Uint8Array(arrayBuffer);
        let hexString = '';
        let nextHexByte;
        if (signedHex && byteArray[0] >= 0x80)
            hexString += '00';
        for (let i = 0; i < byteArray.byteLength; i++) {
            nextHexByte = byteArray[i].toString(16);
            if (nextHexByte.length < 2)
                nextHexByte = '0' + nextHexByte;
            hexString += nextHexByte;
        }
        return hexString;
    },
    hexStringToArrayBuffer: function (hexString) {
        if ((hexString.length % 2) !== 0)
            throw new RangeError('Expected string to be an even number of characters');
        let byteArray = new Uint8Array(hexString.length / 2);
        for (let i = 0; i < hexString.length; i += 2) {
            byteArray[i / 2] = parseInt(hexString.substring(i, i + 2), 16);
        }
        return byteArray.buffer;
    },
    ripemd160: function (bin, byteCt) {
        function binl_rmd160(x, len) {
            x[len >> 5] |= 0x80 << (len % 32);
            x[(((len + 64) >>> 9) << 4) + 14] = len;
            let h0 = 0x67452301;
            let h1 = 0xefcdab89;
            let h2 = 0x98badcfe;
            let h3 = 0x10325476;
            let h4 = 0xc3d2e1f0;
            for (let i = 0; i < x.length; i += 16) {
                let T;
                let A1 = h0, B1 = h1, C1 = h2, D1 = h3, E1 = h4;
                let A2 = h0, B2 = h1, C2 = h2, D2 = h3, E2 = h4;
                for (let j = 0; j <= 79; ++j) {
                    T = safe_add(A1, rmd160_f(j, B1, C1, D1));
                    T = safe_add(T, x[i + rmd160_r1[j]]);
                    T = safe_add(T, rmd160_K1(j));
                    T = safe_add(bit_rol(T, rmd160_s1[j]), E1);
                    A1 = E1; E1 = D1; D1 = bit_rol(C1, 10); C1 = B1; B1 = T;
                    T = safe_add(A2, rmd160_f(79 - j, B2, C2, D2));
                    T = safe_add(T, x[i + rmd160_r2[j]]);
                    T = safe_add(T, rmd160_K2(j));
                    T = safe_add(bit_rol(T, rmd160_s2[j]), E2);
                    A2 = E2; E2 = D2; D2 = bit_rol(C2, 10); C2 = B2; B2 = T;
                }
                T = safe_add(h1, safe_add(C1, D2));
                h1 = safe_add(h2, safe_add(D1, E2));
                h2 = safe_add(h3, safe_add(E1, A2));
                h3 = safe_add(h4, safe_add(A1, B2));
                h4 = safe_add(h0, safe_add(B1, C2));
                h0 = T;
            }
            return [h0, h1, h2, h3, h4];
        }
        function rmd160_f(j, x, y, z) {
            return (0 <= j && j <= 15) ? (x ^ y ^ z) :
                (16 <= j && j <= 31) ? (x & y) | (~x & z) :
                    (32 <= j && j <= 47) ? (x | ~y) ^ z :
                        (48 <= j && j <= 63) ? (x & z) | (y & ~z) :
                            (64 <= j && j <= 79) ? x ^ (y | ~z) :
                                "rmd160_f: j out of range";
        }
        function rmd160_K1(j) {
            return (0 <= j && j <= 15) ? 0x00000000 :
                (16 <= j && j <= 31) ? 0x5a827999 :
                    (32 <= j && j <= 47) ? 0x6ed9eba1 :
                        (48 <= j && j <= 63) ? 0x8f1bbcdc :
                            (64 <= j && j <= 79) ? 0xa953fd4e :
                                "rmd160_K1: j out of range";
        }
        function rmd160_K2(j) {
            return (0 <= j && j <= 15) ? 0x50a28be6 :
                (16 <= j && j <= 31) ? 0x5c4dd124 :
                    (32 <= j && j <= 47) ? 0x6d703ef3 :
                        (48 <= j && j <= 63) ? 0x7a6d76e9 :
                            (64 <= j && j <= 79) ? 0x00000000 :
                                "rmd160_K2: j out of range";
        }
        let rmd160_r1 = [
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15,
            7, 4, 13, 1, 10, 6, 15, 3, 12, 0, 9, 5, 2, 14, 11, 8,
            3, 10, 14, 4, 9, 15, 8, 1, 2, 7, 0, 6, 13, 11, 5, 12,
            1, 9, 11, 10, 0, 8, 12, 4, 13, 3, 7, 15, 14, 5, 6, 2,
            4, 0, 5, 9, 7, 12, 2, 10, 14, 1, 3, 8, 11, 6, 15, 13
        ];
        let rmd160_r2 = [
            5, 14, 7, 0, 9, 2, 11, 4, 13, 6, 15, 8, 1, 10, 3, 12,
            6, 11, 3, 7, 0, 13, 5, 10, 14, 15, 8, 12, 4, 9, 1, 2,
            15, 5, 1, 3, 7, 14, 6, 9, 11, 8, 12, 2, 10, 0, 4, 13,
            8, 6, 4, 1, 3, 11, 15, 0, 5, 12, 2, 13, 9, 7, 10, 14,
            12, 15, 10, 4, 1, 5, 8, 7, 6, 2, 13, 14, 0, 3, 9, 11
        ];
        let rmd160_s1 = [
            11, 14, 15, 12, 5, 8, 7, 9, 11, 13, 14, 15, 6, 7, 9, 8,
            7, 6, 8, 13, 11, 9, 7, 15, 7, 12, 15, 9, 11, 7, 13, 12,
            11, 13, 6, 7, 14, 9, 13, 15, 14, 8, 13, 6, 5, 12, 7, 5,
            11, 12, 14, 15, 14, 15, 9, 8, 9, 14, 5, 6, 8, 6, 5, 12,
            9, 15, 5, 11, 6, 8, 13, 12, 5, 12, 13, 14, 11, 8, 5, 6
        ];
        let rmd160_s2 = [
            8, 9, 9, 11, 13, 15, 15, 5, 7, 7, 8, 11, 14, 14, 12, 6,
            9, 13, 15, 7, 12, 8, 9, 11, 7, 7, 12, 7, 6, 15, 13, 11,
            9, 7, 15, 11, 8, 6, 6, 14, 12, 13, 5, 14, 13, 13, 7, 5,
            15, 5, 8, 11, 14, 14, 6, 14, 6, 9, 12, 9, 12, 5, 15, 8,
            8, 5, 12, 9, 12, 5, 14, 6, 8, 13, 6, 5, 15, 13, 11, 11
        ];
        function safe_add(x, y) {
            let lsw = (x & 0xFFFF) + (y & 0xFFFF);
            let msw = (x >> 16) + (y >> 16) + (lsw >> 16);
            return (msw << 16) | (lsw & 0xFFFF);
        }
        function bit_rol(num, cnt) {
            return (num << cnt) | (num >>> (32 - cnt));
        }
        return binl_rmd160(bin, byteCt * 8);
    },
    sleep: async function (ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
};