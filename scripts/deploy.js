const hre = require("hardhat");

async function main() {
  console.log("Deploying Smart Project Hub contracts to Base Sepolia...");

  // Get deployer account
  const [deployer] = await hre.ethers.getSigners();
  console.log("Deploying contracts with account:", deployer.address);
  console.log("Account balance:", (await deployer.provider.getBalance(deployer.address)).toString());

  // Deploy ProjectOwnership contract
  console.log("\nDeploying ProjectOwnership contract...");
  const ProjectOwnership = await hre.ethers.getContractFactory("ProjectOwnership");
  const projectOwnership = await ProjectOwnership.deploy();
  await projectOwnership.waitForDeployment();
  const projectOwnershipAddress = await projectOwnership.getAddress();
  console.log("ProjectOwnership deployed to:", projectOwnershipAddress);

  // Deploy SoulboundBadge contract
  console.log("\nDeploying SoulboundBadge contract...");
  const SoulboundBadge = await hre.ethers.getContractFactory("SoulboundBadge");
  const soulboundBadge = await SoulboundBadge.deploy();
  await soulboundBadge.waitForDeployment();
  const soulboundBadgeAddress = await soulboundBadge.getAddress();
  console.log("SoulboundBadge deployed to:", soulboundBadgeAddress);

  // Verify deployment
  console.log("\nVerifying deployments...");
  
  // Get initial state
  const totalProjects = await projectOwnership.getTotalProjects();
  console.log("Initial project count:", totalProjects.toString());

  const totalBadges = await soulboundBadge.getTotalBadges();
  console.log("Initial badge count:", totalBadges.toString());

  // Save deployment addresses
  const deploymentInfo = {
    network: "baseSepolia",
    chainId: 84532,
    deployer: deployer.address,
    timestamp: new Date().toISOString(),
    contracts: {
      ProjectOwnership: {
        address: projectOwnershipAddress,
        transactionHash: projectOwnership.deploymentTransaction().hash
      },
      SoulboundBadge: {
        address: soulboundBadgeAddress,
        transactionHash: soulboundBadge.deploymentTransaction().hash
      }
    }
  };

  console.log("\nDeployment Info:", JSON.stringify(deploymentInfo, null, 2));

  // Verify contracts on Etherscan (if API key is provided)
  if (process.env.BASESCAN_API_KEY) {
    console.log("\nVerifying contracts on BaseScan...");
    
    try {
      await hre.run("verify:verify", {
        address: projectOwnershipAddress,
        constructorArguments: [],
        network: "baseSepolia"
      });
      console.log("ProjectOwnership verified on BaseScan");
    } catch (error) {
      console.error("Failed to verify ProjectOwnership:", error.message);
    }

    try {
      await hre.run("verify:verify", {
        address: soulboundBadgeAddress,
        constructorArguments: [],
        network: "baseSepolia"
      });
      console.log("SoulboundBadge verified on BaseScan");
    } catch (error) {
      console.error("Failed to verify SoulboundBadge:", error.message);
    }
  }

  console.log("\n=== Deployment Complete ===");
  console.log("Update your .env file with:");
  console.log(`VITE_PROJECT_OWNERSHIP_CONTRACT=${projectOwnershipAddress}`);
  console.log(`VITE_BADGE_CONTRACT=${soulboundBadgeAddress}`);
}

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error);
    process.exit(1);
  });
