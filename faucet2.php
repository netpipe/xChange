  
  
            <section class="container" style="margin-top:30px; display: flex;align-items: center;justify-content: center;">
        <div class="panel panel-default" style="width: 750px;">
          <div class="panel-heading"><h3>NetCoin(NETC) Faucet</h3></div>
          <div class="panel-body" style="display: flex;flex-direction: column;justify-content: space-evenly;">
            <div style="display: flex;margin: 5px 0; justify-content: space-between;align-items: center;">
              <div style>Enter Your Wallet Address <span style="color:grey; font-weight: bold;">Or</span> Connect Your Wallet</div>
              <button class="btn btn-primary" style="margin: 5px 5px; float:right;" id="conBtn" onclick="connectWallet()">CONNECT</button>
            </div>
            <div style="margin: 5px 0">
              <input type="text" class="form-control container-fluid" id="reqAddress" placeholder="Enter your ERC-20 compatible address (e.g.: 0x…)" aria-describedby="basic-addon1" value="">
            </div>
            <button class="btn btn-success" style="margin: 5px 0" id="reqBtn" onclick="sendRequest()">Request 1 NETC from faucet</button>
            <div class="spinner-border text-primary" role="status">
              <span class="sr-only">Loading...</span>
            </div>
          </div>
        </div>
      </section>  

    <script>
      // If this package is in a subfolder, define the backend path
      // backendPath = "backend/";
      // connect -> fetch_nonce -> sign message-> jwt -> request token with jwt      
      var JWT = '';
      if (typeof(backendPath) == 'undefined') {
        var backendPath = '';
      }
      
      function toggleRequestButton(isLoading) {
        if(isLoading) {
          document.getElementById('reqBtn').disabled = true;
          document.getElementById('conBtn').disabled = true;
          document.getElementById('reqAddress').disabled = true;
        }
        else {
          document.getElementById('reqBtn').disabled = false;
          document.getElementById('conBtn').disabled = false;
          document.getElementById('reqAddress').disabled = false;
        }
      }

      function sendRequest() {
        var address = document.getElementById("reqAddress").value;
        if(!address) {
          alert("Please enter the address");
          return;
        }
        if (!JWT) {
          toggleRequestButton(true);
          axios.post(
            backendPath+"faucet.php",
            {
              reqAddress: address,
              request: 'requestMessage'
            }
          )
          .then(function(response) { 
            if(response.status == 200) {
              let message = response.data.msg;
              handleSignMessage(message, address).then(handleAuthenticate);
              
              function handleSignMessage(msg, addr) {
                return new Promise((resolve, reject) =>  
                  web3.eth.personal.sign(
                    web3.utils.utf8ToHex(message),
                    address,
                    (err, signature) => {
                      if (err) {
                        alert('Failed to sign')
                        toggleRequestButton(false);
                      }
                      return resolve({ address, signature });
                    }
                  )
                );
              }

              function handleAuthenticate({ address, signature }) {
                axios
                  .post(
                    backendPath+"faucet.php",
                    {
                      request: "auth",
                      reqAddress: arguments[0].address,
                      signature: arguments[0].signature
                    }
                  )
                  .then(function(response) {
                    if (response.data.status == "success") { 
                      JWT = response.data.msg;
                      requestToken();
                    }
                    else {
                      JWT = '';
                      alert('Authenticate failed!');
                      toggleRequestButton(false);
                    }
                  })
                  .catch(function(error) {
                    console.error(error);
                  });
              }

            }
            else {
              alert("Network Error! please try it again later")
              toggleRequestButton(false);
            }
          })
          .catch(function(error) {
            alert("Network Error! please try it again later");
            console.error(error);
          });
        }
        else {
          requestToken();
        }
      }

      function requestToken() {
        var address = document.getElementById("reqAddress").value;
        toggleRequestButton(true);
        axios.post(
          backendPath+"faucet.php",
          {
            reqAddress: address,
            request: 'requestToken',
            JWT: JWT            
          }
        )
        .then(function(response) { 
          if(response.status == 200) {
            if(response.data.status === "error") {
              alert(response.data.msg);
              if(response.data.msg.substring(0, 4) == 'Auth') {
                JWT = '';
              }
            }
            else if(response.data.status === "success") {
              alert(response.data.msg);
            }
            else {
              alert("Network Error! please try it again later")
            }

          }
          else {
            alert("Network Error! please try it again later");
          }
          toggleRequestButton(false);
        })
        .catch(function(error) {
          alert("Network Error! please try it again later");
          console.error(error);
        });
      }

      // On accountsChanged
      async function ethAccountsChanged() {      
        let accountsOnEnable = await web3.eth.getAccounts();
        let address = accountsOnEnable[0];
        try {
          JWT = '';
          address = address.toLowerCase();
          if(address) document.getElementById("reqAddress").value = address;
        } catch (e) {
          JWT = '';
          document.getElementById("reqAddress").value = '';
        }
      }

      function ethAccountsDisconnected() {
        document.getElementById("reqAddress").value = '';
      }

      async function connectWallet() {
        document.getElementById("reqAddress").value = '';
        await onConnectLoadWeb3Modal();        
        if (web3ModalProv) {
          try {
            let accountsOnEnable = await ethereum.request({ method: 'eth_accounts' });
            let address = accountsOnEnable[0];
            address = address.toLowerCase();
            document.getElementById("reqAddress").value = address;
          } catch (error) {
            document.getElementById("reqAddress").value = '';
            console.log(error);
            return;
          }
        }
        else {
          return;
        }
      }
    </script>
        <script src="./assets/web3-modal.js"></script>
    <script src="./assets/web3-modal.js"></script>
