<?php

session_start();
    // At the top of the page check to see whether the user is logged in or not
    if(empty($_SESSION['u_id'])){
      
        header("Location: index.html");
        die("Redirecting...");
}

?>

<html>
<head>
<link rel="icon" type="image/png" href="favicon.png"> 
<title>Home</title>

<!-- Works with web3.min.js version 0.19.0. This web page does not work with version 1.0.0 and up -->
<script type="text/javascript" src="js/web3.min.js"></script>
<script type="text/javascript">
"use strict";


let eventHandlerPageLoad = function() {
  // Checking if Web3 has been injected by the browser (Mist/MetaMask)
  if (typeof web3 !== 'undefined') {
    // Use Mist/MetaMask's provider
    window.web3 = new Web3(web3.currentProvider);
  } else {
    // fallback - use your fallback strategy (local node / hosted node + in-dapp id mgmt / fail)
    window.web3 = new Web3(new Web3.providers.HttpProvider("http://localhost:8545"));
  }

  // Immediately execute methods after web page is loaded
  startApp();
}

function startApp(){
  monitorAccountChanges();
  watchBlockInfo();
  watchSyncing();
  reloadPageWhenNoNetwork();
}



window.addEventListener('load', eventHandlerPageLoad);

// Check if an Ethereum node is available every 5 seconds.
// I have chosen arbritray 5 seconds.
function reloadPageWhenNoNetwork(){
  setInterval(function(){
    if(!web3.isConnected()){
      // If an Ethereum node is found, reload web page.
      eventHandlerPageLoad();
    }
  }, 5000);
}

function monitorAccountChanges() {
  // Declare accountInterval here! Clear the variable if there is no Ethereum node found.
  let accountInterval;

  // Check if an Ethereum node is found.
  if(web3.isConnected()){

    // If a coinbase account is found, automatically update the fromAddress form field with the coinbase account
    getCoinbasePromise()
    .then(function(fromAddress){
      document.getElementById('fromAddress').value = fromAddress;
    })
    .catch(function(err){
      document.getElementById('intervalErrorMessage').innerText = err;
    });

    let account = web3.eth.accounts[0];

    // At a time interval of 1 sec monitor account changes
    accountInterval = setInterval(function() {

      // Monitor account changes. If you switch account, for example in MetaMask, it will detect this.
      // See: https://github.com/MetaMask/faq/blob/master/DEVELOPERS.md
      if (web3.eth.accounts[0] !== account) {
        account = web3.eth.accounts[0];
        document.getElementById('fromAddress').value = account;
      } else {
        document.getElementById('intervalErrorMessage').innerText = "No accounts found";
      }
      if(account != null) {
        document.getElementById('intervalErrorMessage').innerText= "";
      }

      // Check which Ethereum network is used
      getNetworkPromise()
      .then(function(network){
        document.getElementById('network').innerText = "Network: " + network + "\n";
      })
      .catch(function(err){
        console.log(err);
      });

    }, 1000); // end of accountInterval = setInterval(function()

  } else {
    // Stop the accountInterval
    clearInterval(accountInterval);
    document.getElementById('intervalErrorMessage').innerText = "No Ethereum node found";
  }
}

// Continuosly watch the latest block and display some block related information on screen.


function createContract(){
  // Each time you modify the DemoContract.sol and deploy it on the blockchain, you need to get the abi value.
  // Paste the abi value in web3.eth.contract(PASTE_ABI_VALUE);
  // When the contract is deployed, do not forget to change the contract address, see
  // formfield id 'contractAddress'
  // Replace contract address: 0xf1d2e0b8e09f4dda7f3fd6db26496f74079faeeb with your own.
  //
  const contractSpec = web3.eth.contract(
  [
  {
    "constant": true,
    "inputs": [],
    "name": "name",
    "outputs": [
      {
        "name": "",
        "type": "string"
      }
    ],
    "payable": false,
    "type": "function"
  },
  {
    "constant": true,
    "inputs": [],
    "name": "decimals",
    "outputs": [
      {
        "name": "",
        "type": "uint8"
      }
    ],
    "payable": false,
    "type": "function"
  },
  {
    "constant": true,
    "inputs": [
      {
        "name": "_owner",
        "type": "address"
      }
    ],
    "name": "balanceOf",
    "outputs": [
      {
        "name": "balance",
        "type": "uint256"
      }
    ],
    "payable": false,
    "type": "function"
  },
  {
    "constant": true,
    "inputs": [],
    "name": "symbol",
    "outputs": [
      {
        "name": "",
        "type": "string"
      }
    ],
    "payable": false,
    "type": "function"
  }
]
  );

  return contractSpec.at(document.getElementById('contractAddress').value);
}


// ===================================================
// Promises
// ===================================================
const getCoinbasePromise = function(){
  return new Promise(function(resolve, reject){
    web3.eth.getCoinbase(function(err, res){
      if (!res) {
        reject("No accounts found");
      } else {
        resolve(res);
      }
    });
  });
}

const checkAddressPromise = function(address, addressType) {
  return new Promise(function(resolve, reject){
    if (address != null && web3.isAddress(address)) {
      resolve();
    } else {
      reject(addressType);
    }
  });
}


const getNetworkPromise = function() {
  return new Promise(function(resolve, reject){
    // Check which Ethereum network is used
    web3.version.getNetwork(function(err, res){
      let selectedNetwork = "";

      if (!err) {
        if(res > 1000000000000) {
          // I am not sure about this. I maybe wrong!
          selectedNetwork = "Testrpc";
        } else {
          switch (res) {
            case "1":
              selectedNetwork = "Mainnet";
              break
            case "2":
              selectedNetwork = "Morden";
              break
            case "3":
              selectedNetwork = "Ropsten";
              break
            case "4":
              selectedNetwork = "Rinkeby";
              break
            default:
              selectedNetwork = "Unknown network = "+res;
          }
        }
        resolve(selectedNetwork);
      } else {
        reject("getBlockTransactionCountPromise: "+err);
      }
    });
  });
}


// ===================================================
// Callback
// ===================================================


// ===================================================
// Helper functions
// ===================================================
function clearOutputs(){
  document.getElementById('result').innerText = "";
  document.getElementById('log').innerText= "";
  document.getElementById('txhash').innerText = "";
  document.getElementById('wait').innerText= "";
  document.getElementById('balanceInfo').innerText= "";
}


function showResult(err, res){
  if(res) {
    document.getElementById('result').innerText = res;
  } else {
    alert("showResult: "+err);
  }
}

// ===================================================
// Calling DemoContract.sol functions
// ===================================================
function getData(clickedId) {
  // Check if Ethereum node is found
  if(web3.isConnected()){
    clearOutputs();

    const contractAddress = document.getElementById('contractAddress').value;
    const fromAddress = document.getElementById('fromAddress').value;

    // Promise chain
    checkAddressPromise(contractAddress, "contract address").then(function(){
      const contract = createContract();


      if(clickedId == "getBalanceBtn"){
        // The balance variable is public. This means a getter is automatically created.
        // Solidity will generate a function of the following form:
        //function balances(address arg1) returns (uint b)
        contract.balanceOf(fromAddress, function (err, res) {
          if(!err) {
            res = web3.fromWei(res, 'ether')+" DMI Tokens";
          }
          showResult(err, res);
        });
      }

    }).catch(function(message){
      document.getElementById('result').innerText = "Not a valid "+message+".";
    });

  } else {
    document.getElementById('intervalErrorMessage').innerText = "No Ethereum node found";
  }
}


function getTotalSupply(){
  const contract = createContract();
  res = contract.totalSupply();
  document.getElementById("totalSupply") = res;
}




</script>

<style type="text/css">

#footer {padding: 15px 0 15px 15px; border-top: 1px solid; border-bottom: 1px solid;}

</style>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">


    <link href = "style.css" rel = "stylesheet" type = "text/css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">

    <title>Cryptonet Technologies</title>
  </head>
  <body></body>

<!--Navbar-->   
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#"><img src = "CryptonetTech.svg" height = "100"></a>
<?php
echo "Welcome, " .$_SESSION['u_first'];
?>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
    </ul>

    <form action = "includes/logout.inc.php" method = "POST">
    <div class="nav navbar-nav navbar-right">
      <button type="submit" name = "logout" class="btn btn-warning">Logout</button>
    </div>
  </form>

  </div>
</nav>

<!-- Header -->
<br></br>
<center>

</center>
<br></br>

<!--Particles Script-->
<div id="particles-js"></div>

<br></br>


<!--Body-->
<div id="content">

<div class = "container">
  <!-- ================= LEFT ================= -->
  <div class = "row">
    <div class = "col">
      <h5>DMI Token Sale Interface</h5><br>
      <h6>The current exchange rate for DMI Tokens to Ether is 500:1.</h6>
      <p>You can trade your Ethereum for DMI Tokens by sending  the desired quantity of Ethereum to your unique contract address. Exchanges of Ethereum for DMI Tokens cannot be reversed. Tokens can be obtained by utilizing the meta-mask browser extension or any ethereum wallet. For a comprehensive walkthrough of this process, click the help button in the navbar.</p>
    </div>




    <div class = "col">
        <h5>Verify the Contract Address</h5>
          <input disabled id='contractAddress' placeholder='contract address' value="0xa98be3d24a0d367b00b075fbf23780e5d2e09c1d" size='50'>
            <br><br>
          <h5>Verify your Personal Address</h5>
          <input id='fromAddress' placeholder='from address' size='50'>
      <br><br>

          <button id="getBalanceBtn" class="btn btn-warning" onclick="getData(this.id)">Get Balance</button>
          
          <br><div id='result'></div>
      </div>
  </div>
</div>

    <br><br>

    <br/><br/>
  </div>
</div>

<div class = "container">
  <div class = "row">
    
    <div class = "col-sm-4">
      <center>
        <h6>Current Supply</h6>
        <p> There are currently</p>
       
        <p> DMI Tokens in Existence </p>
      </center>
    </div>

    <div class = "col-sm-4">
      <center>
        <h6>Time Remaining</h6>
        <p> The first round of our Token sale ends in</p>
        <!--Insert Value-->
        
      </center>
    </div>

    <div class = "col-sm-4">
      <center>
        <h6>Current Rate</h6>
        <p>The exchange rate of Ethereum to DMI Tokens is currently</p>
        <!--Insert Value-->
        <p></p>
      </center>
    </div>

  </div>
</div>


<div id="footer2">
  <div id='txhash'></div>
  <div id='balanceInfo'></div>
  <div id='wait'></div>
  <div id='log'></div>
</div>
</center>

 <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
    <script src="js/particles.js"></script>
    <script src="js/particles.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>