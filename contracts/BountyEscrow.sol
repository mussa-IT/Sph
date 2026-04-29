// SPDX-License-Identifier: MIT
pragma solidity ^0.8.19;

import "@openzeppelin/contracts/security/ReentrancyGuard.sol";
import "@openzeppelin/contracts/access/Ownable.sol";
import "@openzeppelin/contracts/token/ERC20/IERC20.sol";
import "@openzeppelin/contracts/utils/Counters.sol";

/**
 * @title BountyEscrow
 * @dev Smart contract for managing bounty rewards with USDC on Base Sepolia
 * @author Smart Project Hub
 */
contract BountyEscrow is ReentrancyGuard, Ownable {
    using Counters for Counters.Counter;
    
    // Counters for tracking
    Counters.Counter private _bountyIds;
    
    // Bounty status enum
    enum BountyStatus {
        Open,       // 0 - Open for submissions
        Assigned,   // 1 - Winner assigned, awaiting completion
        Completed,  // 2 - Bounty completed, reward released
        Cancelled   // 3 - Bounty cancelled, funds returned
    }
    
    // Bounty struct
    struct Bounty {
        uint256 id;
        address creator;
        address winner;
        uint256 amount;
        address token; // USDC address
        string description;
        BountyStatus status;
        uint256 createdAt;
        uint256 deadline;
        uint256 completedAt;
    }
    
    // Mapping from bounty ID to bounty data
    mapping(uint256 => Bounty) public bounties;
    
    // Mapping from creator to their bounty IDs
    mapping(address => uint256[]) public creatorBounties;
    
    // Mapping from winner to their bounty IDs
    mapping(address => uint256[]) public winnerBounties;
    
    // Events
    event BountyCreated(
        uint256 indexed bountyId,
        address indexed creator,
        uint256 amount,
        string description,
        uint256 deadline
    );
    
    event BountyAssigned(
        uint256 indexed bountyId,
        address indexed winner
    );
    
    event BountyCompleted(
        uint256 indexed bountyId,
        address indexed winner,
        uint256 amount
    );
    
    event BountyCancelled(
        uint256 indexed bountyId,
        address indexed creator,
        uint256 amount
    );
    
    // Errors
    error InvalidAmount();
    error InvalidDeadline();
    error InsufficientBalance();
    error BountyNotFound(uint256 bountyId);
    error UnauthorizedAccess(address caller, address expected);
    error InvalidStatus(BountyStatus current, BountyStatus expected);
    error AlreadyAssigned();
    error NoWinnerAssigned();
    error TransferFailed();
    
    // Constructor
    constructor() Ownable(msg.sender) {
        // Initialize the contract
    }
    
    /**
     * @dev Create a new bounty
     * @param amount Amount in USDC (smallest unit)
     * @param token USDC token address
     * @param description Bounty description
     * @param deadline Unix timestamp for deadline
     */
    function createBounty(
        uint256 amount,
        address token,
        string memory description,
        uint256 deadline
    ) 
        external 
        nonReentrant 
    {
        if (amount == 0) {
            revert InvalidAmount();
        }
        
        if (deadline <= block.timestamp) {
            revert InvalidDeadline();
        }
        
        // Check if creator has sufficient balance
        IERC20 usdcToken = IERC20(token);
        if (usdcToken.balanceOf(msg.sender) < amount) {
            revert InsufficientBalance();
        }
        
        // Transfer USDC to contract
        bool success = usdcToken.transferFrom(msg.sender, address(this), amount);
        if (!success) {
            revert TransferFailed();
        }
        
        // Increment bounty ID
        _bountyIds.increment();
        uint256 newBountyId = _bountyIds.current();
        
        // Create bounty
        bounties[newBountyId] = Bounty({
            id: newBountyId,
            creator: msg.sender,
            winner: address(0),
            amount: amount,
            token: token,
            description: description,
            status: BountyStatus.Open,
            createdAt: block.timestamp,
            deadline: deadline,
            completedAt: 0
        });
        
        // Add to creator's bounty list
        creatorBounties[msg.sender].push(newBountyId);
        
        // Emit event
        emit BountyCreated(
            newBountyId,
            msg.sender,
            amount,
            description,
            deadline
        );
    }
    
    /**
     * @dev Assign a winner to a bounty (only creator)
     * @param bountyId Bounty ID
     * @param winner Winner address
     */
    function assignWinner(uint256 bountyId, address winner) 
        external 
        nonReentrant 
    {
        if (!bounties[bountyId].exists) {
            revert BountyNotFound(bountyId);
        }
        
        Bounty storage bounty = bounties[bountyId];
        
        if (bounty.creator != msg.sender) {
            revert UnauthorizedAccess(msg.sender, bounty.creator);
        }
        
        if (bounty.status != BountyStatus.Open) {
            revert InvalidStatus(bounty.status, BountyStatus.Open);
        }
        
        if (bounty.winner != address(0)) {
            revert AlreadyAssigned();
        }
        
        // Assign winner
        bounty.winner = winner;
        bounty.status = BountyStatus.Assigned;
        
        // Add to winner's bounty list
        winnerBounties[winner].push(bountyId);
        
        // Emit event
        emit BountyAssigned(bountyId, winner);
    }
    
    /**
     * @dev Complete bounty and release reward to winner (only creator)
     * @param bountyId Bounty ID
     */
    function completeBounty(uint256 bountyId) 
        external 
        nonReentrant 
    {
        if (!bounties[bountyId].exists) {
            revert BountyNotFound(bountyId);
        }
        
        Bounty storage bounty = bounties[bountyId];
        
        if (bounty.creator != msg.sender) {
            revert UnauthorizedAccess(msg.sender, bounty.creator);
        }
        
        if (bounty.status != BountyStatus.Assigned) {
            revert InvalidStatus(bounty.status, BountyStatus.Assigned);
        }
        
        if (bounty.winner == address(0)) {
            revert NoWinnerAssigned();
        }
        
        // Update bounty status
        bounty.status = BountyStatus.Completed;
        bounty.completedAt = block.timestamp;
        
        // Transfer reward to winner
        IERC20 usdcToken = IERC20(bounty.token);
        bool success = usdcToken.transfer(bounty.winner, bounty.amount);
        if (!success) {
            revert TransferFailed();
        }
        
        // Emit event
        emit BountyCompleted(bountyId, bounty.winner, bounty.amount);
    }
    
    /**
     * @dev Cancel bounty and return funds to creator (only creator)
     * @param bountyId Bounty ID
     */
    function cancelBounty(uint256 bountyId) 
        external 
        nonReentrant 
    {
        if (!bounties[bountyId].exists) {
            revert BountyNotFound(bountyId);
        }
        
        Bounty storage bounty = bounties[bountyId];
        
        if (bounty.creator != msg.sender) {
            revert UnauthorizedAccess(msg.sender, bounty.creator);
        }
        
        if (bounty.status != BountyStatus.Open) {
            revert InvalidStatus(bounty.status, BountyStatus.Open);
        }
        
        // Update bounty status
        bounty.status = BountyStatus.Cancelled;
        
        // Return funds to creator
        IERC20 usdcToken = IERC20(bounty.token);
        bool success = usdcToken.transfer(bounty.creator, bounty.amount);
        if (!success) {
            revert TransferFailed();
        }
        
        // Emit event
        emit BountyCancelled(bountyId, bounty.creator, bounty.amount);
    }
    
    /**
     * @dev Get bounty information
     * @param bountyId Bounty ID
     * @return Bounty data
     */
    function getBounty(uint256 bountyId) 
        external 
        view 
        returns (Bounty memory) 
    {
        if (!bounties[bountyId].exists) {
            revert BountyNotFound(bountyId);
        }
        
        return bounties[bountyId];
    }
    
    /**
     * @dev Get all bounties created by an address
     * @param creator Creator address
     * @return Array of bounty IDs
     */
    function getCreatorBounties(address creator) 
        external 
        view 
        returns (uint256[] memory) 
    {
        return creatorBounties[creator];
    }
    
    /**
     * @dev Get all bounties won by an address
     * @param winner Winner address
     * @return Array of bounty IDs
     */
    function getWinnerBounties(address winner) 
        external 
        view 
        returns (uint256[] memory) 
    {
        return winnerBounties[winner];
    }
    
    /**
     * @dev Get all open bounties
     * @return Array of bounty IDs
     */
    function getOpenBounties() external view returns (uint256[] memory) {
        uint256 total = _bountyIds.current();
        uint256 openCount = 0;
        
        // Count open bounties
        for (uint256 i = 1; i <= total; i++) {
            if (bounties[i].status == BountyStatus.Open) {
                openCount++;
            }
        }
        
        // Populate array
        uint256[] memory result = new uint256[](openCount);
        uint256 index = 0;
        
        for (uint256 i = 1; i <= total; i++) {
            if (bounties[i].status == BountyStatus.Open) {
                result[index] = i;
                index++;
            }
        }
        
        return result;
    }
    
    /**
     * @dev Get total number of bounties
     * @return Total bounty count
     */
    function getTotalBounties() external view returns (uint256) {
        return _bountyIds.current();
    }
    
    /**
     * @dev Check if a bounty exists
     * @param bountyId Bounty ID
     * @return True if bounty exists
     */
    function bountyExists(uint256 bountyId) external view returns (bool) {
        return bounties[bountyId].exists;
    }
    
    /**
     * @dev Emergency withdrawal (owner only)
     * @param token Token address
     * @param amount Amount to withdraw
     */
    function emergencyWithdraw(address token, uint256 amount) external onlyOwner {
        IERC20(token).transfer(msg.sender, amount);
    }
}
