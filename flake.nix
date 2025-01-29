{
  inputs = {
    nixpkgs.url = "github:cachix/devenv-nixpkgs/rolling";
    systems.url = "github:nix-systems/default";
    devenv.url = "github:cachix/devenv";
    devenv.inputs.nixpkgs.follows = "nixpkgs";
  };

  nixConfig = {
    extra-trusted-public-keys = "devenv.cachix.org-1:w1cLUi8dv3hnoSPGAuibQv+f9TZLr6cv/Hm9XgU50cw=";
    extra-substituters = "https://devenv.cachix.org";
  };

  outputs = { self, nixpkgs, devenv, systems, ... } @ inputs:
    let
      forEachSystem = nixpkgs.lib.genAttrs (import systems);
    in
    {
      packages = forEachSystem (system: {
        devenv-up = self.devShells.${system}.default.config.procfileScript;
        devenv-test = self.devShells.${system}.default.config.test;
      });

      devShells = forEachSystem
        (system:
          let
            pkgs = nixpkgs.legacyPackages.${system};
          in
          {
            default = devenv.lib.mkShell {
              inherit inputs pkgs;
              modules = [
                {
                  # https://devenv.sh/reference/options/
                  packages = [
                    pkgs.php83
                    pkgs.php83Packages.composer
                    pkgs.ddev
                    pkgs.php83Packages.php-codesniffer
                    pkgs.php83Packages.phpstan
                  ];

                  enterShell = ''
                    export TEST=`which phpstan` && echo "phpstan: $TEST" &&  [ -e "$TEST" ] && mkdir -p ~/.composer/vendor/bin && ln -sf $TEST ~/.composer/vendor/bin/phpstan
                    export TEST2=`which phpcs` && echo "phpcs: $TEST2" &&  [ -e "$TEST2" ] && mkdir -p ~/.composer/vendor/bin && ln -sf $TEST ~/.composer/vendor/bin/phpcs
                  '';

                }
              ];
            };
          });
    };
}
