// SPDX-License-Identifier: MIT
pragma solidity ^0.8.19;

import "@openzeppelin/contracts/security/ReentrancyGuard.sol";
import "@openzeppelin/contracts/access/Ownable.sol";
import "@openzeppelin/contracts/utils/Counters.sol";

/**
 * @title ProjectOwnership
 * @dev Smart contract for registering and verifying project ownership on Base Sepolia
 * @author Smart Project Hub
 */
contract ProjectOwnership is ReentrancyGuard, Ownable {
    using Counters for Counters.Counter;
    
    // Counters for tracking
    Counters.Counter private _projectIds;
    
    // Project struct to store project information
    struct Project {
        uint256 id;
        address owner;
        string projectHash;
        string title;
        uint256 timestamp;
        bool exists;
    }
    
    // Mapping from project hash to project data
    mapping(string => Project) public projects;
    
    // Mapping from owner to their project list
    mapping(address => string[]) public ownerProjects;
    
    // Events
    event ProjectRegistered(
        address indexed owner,
        string indexed projectHash,
        string title,
        uint256 timestamp,
        uint256 projectId
    );
    
    event ProjectUpdated(
        address indexed owner,
        string indexed projectHash,
        string newTitle,
        uint256 timestamp
    );
    
    // Errors
    error ProjectAlreadyExists(string projectHash);
    error InvalidProjectHash(string projectHash);
    error UnauthorizedAccess(address caller, address owner);
    error ProjectNotFound(string projectHash);
    
    // Constructor
    constructor() {
        // Initialize the contract
    }
    
    /**
     * @dev Register a new project onchain
     * @param projectHash SHA256 hash of the project data
     * @param title Project title
     */
    function registerProject(string memory projectHash, string memory title) 
        external 
        nonReentrant 
    {
        // Validate project hash (should be 66 characters starting with 0x for keccak256)
        if (bytes(projectHash).length < 10) {
            revert InvalidProjectHash(projectHash);
        }
        
        // Check if project already exists
        if (projects[projectHash].exists) {
            revert ProjectAlreadyExists(projectHash);
        }
        
        // Increment project ID
        _projectIds.increment();
        uint256 newProjectId = _projectIds.current();
        
        // Create project
        projects[projectHash] = Project({
            id: newProjectId,
            owner: msg.sender,
            projectHash: projectHash,
            title: title,
            timestamp: block.timestamp,
            exists: true
        });
        
        // Add to owner's project list
        ownerProjects[msg.sender].push(projectHash);
        
        // Emit event
        emit ProjectRegistered(
            msg.sender,
            projectHash,
            title,
            block.timestamp,
            newProjectId
        );
    }
    
    /**
     * @dev Update project title (only owner can update)
     * @param projectHash Project hash
     * @param newTitle New project title
     */
    function updateProjectTitle(string memory projectHash, string memory newTitle) 
        external 
        nonReentrant 
    {
        if (!projects[projectHash].exists) {
            revert ProjectNotFound(projectHash);
        }
        
        if (projects[projectHash].owner != msg.sender) {
            revert UnauthorizedAccess(msg.sender, projects[projectHash].owner);
        }
        
        // Update title
        projects[projectHash].title = newTitle;
        
        // Emit event
        emit ProjectUpdated(
            msg.sender,
            projectHash,
            newTitle,
            block.timestamp
        );
    }
    
    /**
     * @dev Get project information
     * @param projectHash Project hash
     * @return Project data
     */
    function getProject(string memory projectHash) 
        external 
        view 
        returns (Project memory) 
    {
        if (!projects[projectHash].exists) {
            revert ProjectNotFound(projectHash);
        }
        
        return projects[projectHash];
    }
    
    /**
     * @dev Get all projects owned by an address
     * @param owner Owner address
     * @return Array of project hashes
     */
    function getOwnerProjects(address owner) 
        external 
        view 
        returns (string[] memory) 
    {
        return ownerProjects[owner];
    }
    
    /**
     * @dev Get total number of registered projects
     * @return Total project count
     */
    function getTotalProjects() external view returns (uint256) {
        return _projectIds.current();
    }
    
    /**
     * @dev Check if a project exists
     * @param projectHash Project hash
     * @return True if project exists
     */
    function projectExists(string memory projectHash) external view returns (bool) {
        return projects[projectHash].exists;
    }
    
    /**
     * @dev Get project owner
     * @param projectHash Project hash
     * @return Owner address
     */
    function getProjectOwner(string memory projectHash) external view returns (address) {
        if (!projects[projectHash].exists) {
            revert ProjectNotFound(projectHash);
        }
        
        return projects[projectHash].owner;
    }
    
    /**
     * @dev Get project registration timestamp
     * @param projectHash Project hash
     * @return Registration timestamp
     */
    function getProjectTimestamp(string memory projectHash) external view returns (uint256) {
        if (!projects[projectHash].exists) {
            revert ProjectNotFound(projectHash);
        }
        
        return projects[projectHash].timestamp;
    }
    
    /**
     * @dev Admin function to remove a project (emergency only)
     * @param projectHash Project hash to remove
     */
    function removeProject(string memory projectHash) external onlyOwner {
        if (!projects[projectHash].exists) {
            revert ProjectNotFound(projectHash);
        }
        
        address owner = projects[projectHash].owner;
        
        // Remove from projects mapping
        delete projects[projectHash];
        
        // Remove from owner's project list
        string[] storage projectList = ownerProjects[owner];
        for (uint256 i = 0; i < projectList.length; i++) {
            if (keccak256(bytes(projectList[i])) == keccak256(bytes(projectHash))) {
                projectList[i] = projectList[projectList.length - 1];
                projectList.pop();
                break;
            }
        }
    }
}
