// SPDX-License-Identifier: MIT
pragma solidity ^0.8.19;

import "@openzeppelin/contracts/token/ERC721/ERC721.sol";
import "@openzeppelin/contracts/token/ERC721/extensions/ERC721URIStorage.sol";
import "@openzeppelin/contracts/access/Ownable.sol";
import "@openzeppelin/contracts/utils/Counters.sol";
import "@openzeppelin/contracts/security/ReentrancyGuard.sol";

/**
 * @title SoulboundBadge
 * @dev Non-transferable NFT contract for achievement badges
 * @author Smart Project Hub
 */
contract SoulboundBadge is ERC721, ERC721URIStorage, Ownable, ReentrancyGuard {
    using Counters for Counters.Counter;
    
    // Badge types
    enum BadgeType {
        PROJECT_COMPLETED,    // 1
        TOP_INNOVATOR,        // 2
        MENTOR,               // 3
        TASKS_100,            // 4
        EARLY_ADOPTER,        // 5
        VERIFIED_BUILDER     // 6
    }
    
    // Badge metadata
    struct BadgeMetadata {
        BadgeType badgeType;
        string name;
        string description;
        string icon;
        string rarity;
        bool isActive;
    }
    
    // Token ID counter
    Counters.Counter private _tokenIds;
    
    // Mapping from badge type to metadata
    mapping(BadgeType => BadgeMetadata) public badgeMetadata;
    
    // Mapping from address to badge types they own
    mapping(address => mapping(BadgeType => uint256)) public userBadges;
    
    // Mapping from token ID to badge type
    mapping(uint256 => BadgeType) public tokenBadgeTypes;
    
    // Events
    event BadgeMinted(
        address indexed to,
        uint256 indexed tokenId,
        BadgeType indexed badgeType,
        string badgeName,
        uint256 timestamp
    );
    
    event BadgeMetadataUpdated(
        BadgeType indexed badgeType,
        string name,
        string description,
        string rarity
    );
    
    // Errors
    error BadgeTypeNotActive(BadgeType badgeType);
    error BadgeAlreadyMinted(address user, BadgeType badgeType);
    error InvalidBadgeType(BadgeType badgeType);
    error TransferNotAllowed();
    error ApprovalNotAllowed();
    error TokenDoesNotExist(uint256 tokenId);
    
    // Constructor
    constructor() ERC721("Soulbound Badges", "SBB") {
        // Initialize badge metadata
        _initializeBadgeMetadata();
    }
    
    /**
     * @dev Initialize default badge metadata
     */
    function _initializeBadgeMetadata() internal {
        badgeMetadata[BadgeType.PROJECT_COMPLETED] = BadgeMetadata({
            badgeType: BadgeType.PROJECT_COMPLETED,
            name: "Project Completed",
            description: "Successfully completed a project",
            icon: "🎯",
            rarity: "common",
            isActive: true
        });
        
        badgeMetadata[BadgeType.TOP_INNOVATOR] = BadgeMetadata({
            badgeType: BadgeType.TOP_INNOVATOR,
            name: "Top Innovator",
            description: "Recognized for innovative solutions",
            icon: "💡",
            rarity: "rare",
            isActive: true
        });
        
        badgeMetadata[BadgeType.MENTOR] = BadgeMetadata({
            badgeType: BadgeType.MENTOR,
            name: "Mentor",
            description: "Helped guide other builders",
            icon: "👥",
            rarity: "rare",
            isActive: true
        });
        
        badgeMetadata[BadgeType.TASKS_100] = BadgeMetadata({
            badgeType: BadgeType.TASKS_100,
            name: "Task Master",
            description: "Completed 100+ tasks",
            icon: "✅",
            rarity: "uncommon",
            isActive: true
        });
        
        badgeMetadata[BadgeType.EARLY_ADOPTER] = BadgeMetadata({
            badgeType: BadgeType.EARLY_ADOPTER,
            name: "Early Adopter",
            description: "One of the first Web3 users",
            icon: "🚀",
            rarity: "rare",
            isActive: true
        });
        
        badgeMetadata[BadgeType.VERIFIED_BUILDER] = BadgeMetadata({
            badgeType: BadgeType.VERIFIED_BUILDER,
            name: "Verified Builder",
            description: "Verified project on blockchain",
            icon: "🔗",
            rarity: "common",
            isActive: true
        });
    }
    
    /**
     * @dev Mint a badge to a user (admin only)
     * @param to Recipient address
     * @param badgeType Type of badge to mint
     * @param tokenURI Metadata URI for the badge
     */
    function mintBadge(
        address to, 
        BadgeType badgeType, 
        string memory tokenURI
    ) 
        external 
        onlyOwner 
        nonReentrant 
    {
        if (!badgeMetadata[badgeType].isActive) {
            revert BadgeTypeNotActive(badgeType);
        }
        
        if (userBadges[to][badgeType] != 0) {
            revert BadgeAlreadyMinted(to, badgeType);
        }
        
        _tokenIds.increment();
        uint256 newTokenId = _tokenIds.current();
        
        // Mint the NFT
        _safeMint(to, newTokenId);
        _setTokenURI(newTokenId, tokenURI);
        
        // Track badge type
        tokenBadgeTypes[newTokenId] = badgeType;
        userBadges[to][badgeType] = newTokenId;
        
        // Emit event
        emit BadgeMinted(
            to,
            newTokenId,
            badgeType,
            badgeMetadata[badgeType].name,
            block.timestamp
        );
    }
    
    /**
     * @dev Update badge metadata (admin only)
     * @param badgeType Badge type to update
     * @param name New name
     * @param description New description
     * @param rarity New rarity
     */
    function updateBadgeMetadata(
        BadgeType badgeType,
        string memory name,
        string memory description,
        string memory rarity
    ) external onlyOwner {
        badgeMetadata[badgeType].name = name;
        badgeMetadata[badgeType].description = description;
        badgeMetadata[badgeType].rarity = rarity;
        
        emit BadgeMetadataUpdated(badgeType, name, description, rarity);
    }
    
    /**
     * @dev Activate/deactivate a badge type (admin only)
     * @param badgeType Badge type to toggle
     * @param isActive Active status
     */
    function setBadgeTypeActive(BadgeType badgeType, bool isActive) external onlyOwner {
        badgeMetadata[badgeType].isActive = isActive;
    }
    
    /**
     * @dev Get badge metadata
     * @param badgeType Badge type
     * @return Badge metadata
     */
    function getBadgeMetadata(BadgeType badgeType) external view returns (BadgeMetadata memory) {
        return badgeMetadata[badgeType];
    }
    
    /**
     * @dev Get all badges owned by an address
     * @param owner Owner address
     * @return Array of badge types
     */
    function getUserBadges(address owner) external view returns (BadgeType[] memory) {
        uint256 count = 0;
        
        // Count active badges
        for (uint256 i = 1; i <= 6; i++) {
            BadgeType badgeType = BadgeType(i);
            if (userBadges[owner][badgeType] != 0 && badgeMetadata[badgeType].isActive) {
                count++;
            }
        }
        
        BadgeType[] memory result = new BadgeType[](count);
        uint256 index = 0;
        
        // Populate array
        for (uint256 i = 1; i <= 6; i++) {
            BadgeType badgeType = BadgeType(i);
            if (userBadges[owner][badgeType] != 0 && badgeMetadata[badgeType].isActive) {
                result[index] = badgeType;
                index++;
            }
        }
        
        return result;
    }
    
    /**
     * @dev Check if user owns a specific badge
     * @param user User address
     * @param badgeType Badge type
     * @return True if user owns the badge
     */
    function hasBadge(address user, BadgeType badgeType) external view returns (bool) {
        return userBadges[user][badgeType] != 0;
    }
    
    /**
     * @dev Get token ID for user's badge
     * @param user User address
     * @param badgeType Badge type
     * @return Token ID or 0 if not owned
     */
    function getUserBadgeToken(address user, BadgeType badgeType) external view returns (uint256) {
        return userBadges[user][badgeType];
    }
    
    /**
     * @dev Get total number of badges minted
     * @return Total badge count
     */
    function getTotalBadges() external view returns (uint256) {
        return _tokenIds.current();
    }
    
    // Override transfer functions to make tokens soulbound (non-transferable)
    
    function _beforeTokenTransfer(
        address from,
        address to,
        uint256 tokenId,
        uint256 batchSize
    ) internal pure override {
        // Allow minting (from = address(0)) but prevent any other transfers
        if (from != address(0) && to != address(0)) {
            revert TransferNotAllowed();
        }
    }
    
    function transferFrom(address from, address to, uint256 tokenId) public pure override {
        revert TransferNotAllowed();
    }
    
    function safeTransferFrom(address from, address to, uint256 tokenId) public pure override {
        revert TransferNotAllowed();
    }
    
    function safeTransferFrom(address from, address to, uint256 tokenId, bytes memory _data) public pure override {
        revert TransferNotAllowed();
    }
    
    function approve(address to, uint256 tokenId) public pure override {
        revert ApprovalNotAllowed();
    }
    
    function setApprovalForAll(address operator, bool approved) public pure override {
        revert ApprovalNotAllowed();
    }
    
    // Required overrides
    function supportsInterface(bytes4 interfaceId) 
        public 
        view 
        override(ERC721, ERC721URIStorage) 
        returns (bool) 
    {
        return super.supportsInterface(interfaceId);
    }
    
    function tokenURI(uint256 tokenId) 
        public 
        view 
        override(ERC721, ERC721URIStorage) 
        returns (string memory) 
    {
        return super.tokenURI(tokenId);
    }
    
    function _burn(uint256 tokenId) internal override(ERC721, ERC721URIStorage) {
        super._burn(tokenId);
    }
}
