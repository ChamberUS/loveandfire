import React, { useRef, useState } from "react";
import {
  StatusBar,
  View,
  Text,
  ActivityIndicator,
  TouchableOpacity,
  StyleSheet,
  BackHandler,
  Platform,
  Linking,
} from "react-native";
import { WebView } from "react-native-webview";
import {
  SafeAreaProvider,
  SafeAreaView,
} from "react-native-safe-area-context";

export default function App() {
  const webViewRef = useRef(null);

  const siteUrl = "https://lovenfire.com";

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  const [canGoBack, setCanGoBack] = useState(false);
  const [reloadKey, setReloadKey] = useState(0);
  const [progress, setProgress] = useState(0);

  React.useEffect(() => {
    if (Platform.OS !== "android") {
      return;
    }

    const backAction = () => {
      if (canGoBack && webViewRef.current) {
        webViewRef.current.goBack();
        return true;
      }

      return false;
    };

    const backHandler = BackHandler.addEventListener(
      "hardwareBackPress",
      backAction
    );

    return () => backHandler.remove();
  }, [canGoBack]);

  function tentarNovamente() {
    setError(false);
    setErrorMessage("");
    setLoading(true);
    setProgress(0);
    setReloadKey((prev) => prev + 1);
  }

  function abrirNoNavegador() {
    Linking.openURL(siteUrl).catch(() => {});
  }

  function isLovenFireUrl(url) {
    const value = String(url || "").toLowerCase();

    if (value.indexOf("about:blank") === 0) {
      return true;
    }

    if (value.indexOf("https://lovenfire.com") === 0) {
      return true;
    }

    if (value.indexOf("https://www.lovenfire.com") === 0) {
      return true;
    }

    return false;
  }

  function handleExternalNavigation(request) {
    const url = String(request && request.url ? request.url : "");

    if (isLovenFireUrl(url)) {
      return true;
    }

    if (
      url.indexOf("mailto:") === 0 ||
      url.indexOf("tel:") === 0 ||
      url.indexOf("sms:") === 0 ||
      url.indexOf("whatsapp:") === 0 ||
      url.indexOf("https://wa.me/") === 0
    ) {
      Linking.openURL(url).catch(() => {});
      return false;
    }

    if (url.indexOf("http://") === 0 || url.indexOf("https://") === 0) {
      Linking.openURL(url).catch(() => {});
      return false;
    }

    return true;
  }

  const injectedJavaScript = `
    (function() {
      var meta = document.querySelector('meta[name="viewport"]');

      if (!meta) {
        meta = document.createElement('meta');
        meta.name = 'viewport';
        document.head.appendChild(meta);
      }

      meta.setAttribute(
        'content',
        'width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover'
      );

      document.documentElement.style.backgroundColor = '#050505';
      document.body.style.backgroundColor = '#050505';

      true;
    })();
  `;

  const progressWidth = `${Math.max(
    7,
    Math.min(100, Math.round(progress * 100))
  )}%`;

  return (
    <SafeAreaProvider>
      <SafeAreaView
        style={styles.container}
        edges={["top", "left", "right", "bottom"]}
      >
        <StatusBar barStyle="light-content" backgroundColor="#050505" />

        {error ? (
          <View style={styles.errorContainer}>
            <View style={styles.orbOne} />
            <View style={styles.orbTwo} />
            <View style={styles.orbThree} />

            <View style={styles.errorCard}>
              <View style={styles.brandBadge}>
                <Text style={styles.brandIcon}>❤️‍🔥</Text>
              </View>

              <Text style={styles.title}>LovenFire</Text>
              <Text style={styles.subtitle}>Powered by Buynnex</Text>

              <View style={styles.divider} />

              <Text style={styles.errorTitle}>Sem conexão</Text>

              <Text style={styles.errorText}>
                Não foi possível carregar o LovenFire. Verifique sua internet e
                tente novamente.
              </Text>

              {errorMessage ? (
                <Text style={styles.errorSmall}>{errorMessage}</Text>
              ) : null}

              <TouchableOpacity
                activeOpacity={0.86}
                style={styles.button}
                onPress={tentarNovamente}
              >
                <Text style={styles.buttonText}>Tentar novamente</Text>
              </TouchableOpacity>

              <TouchableOpacity
                activeOpacity={0.86}
                style={styles.secondaryButton}
                onPress={abrirNoNavegador}
              >
                <Text style={styles.secondaryButtonText}>
                  Abrir no navegador
                </Text>
              </TouchableOpacity>
            </View>
          </View>
        ) : (
          <>
            <WebView
              key={reloadKey}
              ref={webViewRef}
              source={{ uri: siteUrl }}
              originWhitelist={["https://*", "http://*"]}
              javaScriptEnabled={true}
              domStorageEnabled={true}
              sharedCookiesEnabled={true}
              thirdPartyCookiesEnabled={true}
              cacheEnabled={true}
              startInLoadingState={false}
              allowsBackForwardNavigationGestures={true}
              pullToRefreshEnabled={true}
              setSupportMultipleWindows={false}
              allowsInlineMediaPlayback={true}
              mediaPlaybackRequiresUserAction={false}
              injectedJavaScript={injectedJavaScript}
              onShouldStartLoadWithRequest={handleExternalNavigation}
              onNavigationStateChange={(navState) => {
                setCanGoBack(navState.canGoBack);
              }}
              onLoadProgress={({ nativeEvent }) => {
                setProgress(nativeEvent.progress || 0);
              }}
              onLoadStart={() => {
                setLoading(true);
                setError(false);
                setErrorMessage("");
              }}
              onLoadEnd={() => {
                setLoading(false);
              }}
              onError={({ nativeEvent }) => {
                setLoading(false);
                setError(true);
                setErrorMessage(
                  nativeEvent && nativeEvent.description
                    ? nativeEvent.description
                    : ""
                );
              }}
              onHttpError={({ nativeEvent }) => {
                if (nativeEvent && nativeEvent.statusCode >= 500) {
                  setLoading(false);
                  setError(true);
                  setErrorMessage(
                    "Erro HTTP " + String(nativeEvent.statusCode)
                  );
                }
              }}
              style={styles.webview}
            />

            {loading && (
              <View pointerEvents="auto" style={styles.loadingContainer}>
                <View style={styles.orbOne} />
                <View style={styles.orbTwo} />
                <View style={styles.orbThree} />

                <View style={styles.loadingBox}>
                  <View style={styles.brandBadgeSmall}>
                    <Text style={styles.loadingLogo}>❤️‍🔥</Text>
                  </View>

                  <Text style={styles.loadingTitle}>LovenFire</Text>
                  <Text style={styles.loadingSubtitle}>
                    Conectando corações...
                  </Text>

                  <ActivityIndicator
                    size="large"
                    color="#ffffff"
                    style={styles.loadingIndicator}
                  />

                  <View style={styles.progressTrack}>
                    <View
                      style={[
                        styles.progressFill,
                        {
                          width: progressWidth,
                        },
                      ]}
                    />
                  </View>

                  <Text style={styles.loadingText}>
                    Carregando experiência premium
                  </Text>
                </View>
              </View>
            )}
          </>
        )}
      </SafeAreaView>
    </SafeAreaProvider>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#050505",
  },

  webview: {
    flex: 1,
    backgroundColor: "#050505",
  },

  loadingContainer: {
    position: "absolute",
    top: 0,
    right: 0,
    bottom: 0,
    left: 0,
    backgroundColor: "rgba(5,5,5,0.82)",
    alignItems: "center",
    justifyContent: "center",
    padding: 26,
    overflow: "hidden",
  },

  loadingBox: {
    width: "100%",
    maxWidth: 315,
    paddingVertical: 30,
    paddingHorizontal: 24,
    borderRadius: 34,
    backgroundColor: "rgba(18, 11, 15, 0.96)",
    alignItems: "center",
    borderWidth: 1,
    borderColor: "rgba(255,255,255,0.10)",
    shadowColor: "#ff315f",
    shadowOpacity: 0.42,
    shadowRadius: 30,
    shadowOffset: {
      width: 0,
      height: 18,
    },
    elevation: 16,
  },

  brandBadgeSmall: {
    width: 78,
    height: 78,
    borderRadius: 28,
    backgroundColor: "rgba(255,255,255,0.08)",
    alignItems: "center",
    justifyContent: "center",
    marginBottom: 14,
    borderWidth: 1,
    borderColor: "rgba(255,255,255,0.12)",
  },

  loadingLogo: {
    fontSize: 42,
  },

  loadingTitle: {
    color: "#ffffff",
    fontSize: 34,
    fontWeight: "900",
    letterSpacing: -1,
  },

  loadingSubtitle: {
    color: "rgba(255,255,255,0.72)",
    fontSize: 14,
    fontWeight: "800",
    marginTop: 5,
  },

  loadingIndicator: {
    marginTop: 22,
  },

  progressTrack: {
    width: "100%",
    height: 8,
    borderRadius: 999,
    backgroundColor: "rgba(255,255,255,0.12)",
    overflow: "hidden",
    marginTop: 24,
  },

  progressFill: {
    height: 8,
    borderRadius: 999,
    backgroundColor: "#ff315f",
  },

  loadingText: {
    color: "rgba(255,255,255,0.76)",
    fontSize: 13,
    fontWeight: "800",
    marginTop: 14,
    textAlign: "center",
  },

  errorContainer: {
    flex: 1,
    backgroundColor: "#050505",
    alignItems: "center",
    justifyContent: "center",
    padding: 26,
    overflow: "hidden",
  },

  errorCard: {
    width: "100%",
    maxWidth: 330,
    paddingVertical: 32,
    paddingHorizontal: 24,
    borderRadius: 34,
    backgroundColor: "rgba(18, 11, 15, 0.96)",
    alignItems: "center",
    borderWidth: 1,
    borderColor: "rgba(255,255,255,0.10)",
    shadowColor: "#ff315f",
    shadowOpacity: 0.34,
    shadowRadius: 28,
    shadowOffset: {
      width: 0,
      height: 18,
    },
    elevation: 16,
  },

  brandBadge: {
    width: 92,
    height: 92,
    borderRadius: 32,
    backgroundColor: "rgba(255,255,255,0.08)",
    alignItems: "center",
    justifyContent: "center",
    marginBottom: 16,
    borderWidth: 1,
    borderColor: "rgba(255,255,255,0.12)",
  },

  brandIcon: {
    fontSize: 54,
  },

  title: {
    color: "#ffffff",
    fontSize: 38,
    fontWeight: "900",
    letterSpacing: -1,
  },

  subtitle: {
    color: "#8fcce4",
    fontSize: 11,
    fontWeight: "900",
    letterSpacing: 1.4,
    textTransform: "uppercase",
    marginTop: 5,
  },

  divider: {
    width: 54,
    height: 3,
    borderRadius: 999,
    backgroundColor: "#ff315f",
    marginVertical: 24,
  },

  errorTitle: {
    color: "#ffffff",
    fontSize: 24,
    fontWeight: "900",
    marginBottom: 10,
    textAlign: "center",
  },

  errorText: {
    color: "rgba(255,255,255,0.74)",
    fontSize: 15,
    textAlign: "center",
    lineHeight: 23,
    marginBottom: 14,
    fontWeight: "700",
  },

  errorSmall: {
    color: "rgba(255,255,255,0.44)",
    fontSize: 12,
    textAlign: "center",
    lineHeight: 18,
    marginBottom: 18,
  },

  button: {
    width: "100%",
    minHeight: 52,
    backgroundColor: "#ff315f",
    alignItems: "center",
    justifyContent: "center",
    borderRadius: 999,
    shadowColor: "#ff315f",
    shadowOpacity: 0.45,
    shadowRadius: 18,
    shadowOffset: {
      width: 0,
      height: 10,
    },
    elevation: 10,
    marginTop: 8,
  },

  buttonText: {
    color: "#ffffff",
    fontSize: 16,
    fontWeight: "900",
  },

  secondaryButton: {
    width: "100%",
    minHeight: 50,
    backgroundColor: "rgba(255,255,255,0.08)",
    alignItems: "center",
    justifyContent: "center",
    borderRadius: 999,
    borderWidth: 1,
    borderColor: "rgba(255,255,255,0.10)",
    marginTop: 12,
  },

  secondaryButtonText: {
    color: "rgba(255,255,255,0.82)",
    fontSize: 15,
    fontWeight: "900",
  },

  orbOne: {
    position: "absolute",
    width: 260,
    height: 260,
    borderRadius: 130,
    backgroundColor: "rgba(255,49,95,0.28)",
    top: -70,
    right: -90,
  },

  orbTwo: {
    position: "absolute",
    width: 230,
    height: 230,
    borderRadius: 115,
    backgroundColor: "rgba(255,122,47,0.18)",
    bottom: -80,
    left: -70,
  },

  orbThree: {
    position: "absolute",
    width: 160,
    height: 160,
    borderRadius: 80,
    backgroundColor: "rgba(143,204,228,0.14)",
    top: "22%",
    left: -80,
  },
});