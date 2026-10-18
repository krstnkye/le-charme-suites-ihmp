# Le Charme Suites IHMS

The Laravel application is inside the `le-charme-suites-ihms` folder.

## Windows Quick Start

1. Install Git, PHP 8.3 or newer, and Composer.
2. Clone this repository.
3. Open the cloned repository folder in Visual Studio Code.
4. Double-click `setup-and-run.bat`, or run it from the VS Code terminal.
5. Open <http://127.0.0.1:8000>.

For later GitHub updates, double-click `update-and-run.bat`. It pulls the latest
`main` branch, installs changed PHP dependencies, updates the database, clears
stale Laravel caches, and starts the local server.

## Shared Offline / Local Network Use

After completing setup, the system can run without internet because its
database and interface assets are local. To let several laptops use one shared
database on the same private Wi-Fi network, double-click
`run-shared-lan-server.bat` on the host laptop and follow the address shown in
the terminal. Keep that terminal and the host laptop running during use.

Detailed setup and troubleshooting instructions are in
[`le-charme-suites-ihms/GROUPMATES_SETUP.md`](le-charme-suites-ihms/GROUPMATES_SETUP.md).
