document.addEventListener('DOMContentLoaded', async function () {
    if (typeof window.ethereum === 'undefined') {
        alert("🦊 MetaMask ni nameščen. Namesti ga za uporabo tega vtičnika.");
        return;
    }

    const web3 = new Web3(window.ethereum);

    try {
        await window.ethereum.request({ method: 'eth_requestAccounts' });
    } catch (e) {
        console.error("❌ MetaMask povezava zavrnjena.");
        return;
    }

    const accounts = await web3.eth.getAccounts();
    const account = accounts[0];

    const abiResponse = await fetch('/wp-content/plugins/ducky-nft-plugin/abi.json');
    const abi = await abiResponse.json();

    const contract = new web3.eth.Contract(abi, duckyNFTData.contractAddress);

    let balance = 0;
    try {
        balance = await contract.methods.balanceOf(account).call();
    } catch (err) {
        console.error("❌ Napaka pri balanceOf:", err);
    }

    console.log("📤 Pošiljam podatke v WP:", {
        user_id: duckyNFTData.userId,
        eth_address: account,
        nft_balance: balance
    });

    jQuery.post(duckyNFTData.ajaxUrl, {
        action: 'save_user_eth_data',
        eth_address: account,
        nft_balance: balance
    });

    // UI prikaz
    const statusDiv = document.getElementById('nft-status');
    const imgDiv = document.getElementById('nft-image');

    if (statusDiv) {
        if (balance > 0) {
            statusDiv.innerText = `✅ Imaš ${balance} Ducky NFT(jev).`;

            try {
                const tokenId = 0;
                const uri = await contract.methods.tokenURI(tokenId).call();

                const img = document.createElement('img');
                img.src = uri;
                img.alt = "Tvoj Ducky NFT";
                img.style.maxWidth = '300px';
                img.style.marginTop = '10px';

                if (imgDiv) imgDiv.appendChild(img);
            } catch (e) {
                console.error("❌ Napaka pri tokenURI:", e);
                if (imgDiv) imgDiv.innerText = "Napaka pri nalaganju slike NFT.";
            }
        } else {
            statusDiv.innerText = "ℹ️ Še nimaš NFT-ja. Mintaj ga spodaj!";
        }
    }

    const mintBtn = document.getElementById('mint-nft');
    if (mintBtn) {
        mintBtn.addEventListener('click', async () => {
            try {
                await contract.methods.mintAnNFT().send({
                    from: account,
                    value: web3.utils.toWei('0.0025', 'ether')
                });
                alert("🎉 NFT uspešno mintan!");
                location.reload();
            } catch (err) {
                console.error("❌ Mintanje ni uspelo:", err);
                alert("Mintanje ni uspelo. Preveri MetaMask.");
            }
        });
    }
});
