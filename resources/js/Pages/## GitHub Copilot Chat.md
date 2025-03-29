## GitHub Copilot Chat

- Extension Version: 0.25.1 (prod)
- VS Code: vscode/1.98.2
- OS: Linux

## Network

User Settings:
```json
  "github.copilot.advanced.debug.useElectronFetcher": true,
  "github.copilot.advanced.debug.useNodeFetcher": false,
  "github.copilot.advanced.debug.useNodeFetchFetcher": true
```

Connecting to https://api.github.com:
- DNS ipv4 Lookup: 20.87.245.6 (120 ms)
- DNS ipv6 Lookup: Error (15 ms): getaddrinfo EBUSY api.github.com
- Proxy URL: None (0 ms)
- Electron fetch (configured): HTTP 200 (494 ms)
- Node.js https: HTTP 200 (510 ms)
- Node.js fetch: HTTP 200 (785 ms)
- Helix fetch: HTTP 200 (430 ms)

Connecting to https://api.individual.githubcopilot.com/_ping:
- DNS ipv4 Lookup: 140.82.113.22 (139 ms)
- DNS ipv6 Lookup: Error (13 ms): getaddrinfo EBUSY api.individual.githubcopilot.com
- Proxy URL: None (2 ms)
- Electron fetch (configured): timed out after 10 seconds
- Node.js https: HTTP 200 (955 ms)
- Node.js fetch: timed out after 10 seconds
- Helix fetch: HTTP 200 (1286 ms)

## Documentation

In corporate networks: [Troubleshooting firewall settings for GitHub Copilot](https://docs.github.com/en/copilot/troubleshooting-github-copilot/troubleshooting-firewall-settings-for-github-copilot).