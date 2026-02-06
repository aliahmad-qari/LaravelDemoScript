
import React, { useState, useEffect } from 'react';
import { 
  ShieldCheck, 
  Terminal, 
  Activity, 
  Lock, 
  LogOut, 
  LayoutDashboard,
  Code,
  Globe,
  Monitor,
  FolderOpen,
  FileCode,
  AlertTriangle,
  Zap,
  Timer,
  Trash2,
  Calendar,
  UserPlus,
  LogIn,
  Smartphone,
  Gauge,
  Info,
  ShieldAlert,
  User as UserIcon
} from 'lucide-react';

// --- Simulator Types ---
interface User { id: number; name: string; email: string; password: string; }
interface LoginLog { id: number; userId: number | null; ipAddress: string; status: 'success' | 'failed' | 'blocked'; timestamp: string; }
interface LoginIp { id: number; userId: number; ipAddress: string; userAgent: string; country: string; timestamp: string; }

const INITIAL_USERS: User[] = [
  { id: 1, name: 'Admin User', email: 'admin@example.com', password: 'password123' },
  { id: 2, name: 'Demo User', email: 'user@example.com', password: 'password123' }
];

const LARAVEL_FILES = [
  { path: 'app/Models/User.php', lang: 'php' },
  { path: 'app/Models/LoginIp.php', lang: 'php' },
  { path: 'app/Models/LoginLog.php', lang: 'php' },
  { path: 'app/Services/SecurityService.php', lang: 'php' },
  { path: 'app/Http/Controllers/AuthController.php', lang: 'php' },
  { path: 'app/Http/Middleware/IpRestrictionMiddleware.php', lang: 'php' },
  { path: 'resources/views/admin/logs.blade.php', lang: 'php' },
  { path: 'resources/views/auth/register.blade.php', lang: 'php' },
  { path: 'README.md', lang: 'markdown' }
];

const MOCK_COUNTRIES: Record<string, string> = {
  '1.1.1.1': 'Australia',
  '8.8.8.8': 'United States',
  '123.123.123.123': 'Japan',
  '45.45.45.45': 'Germany'
};

const UA_STRINGS = {
  'chrome': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
  'firefox': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0',
  'safari': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15'
};

const App: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'simulator' | 'code'>('simulator');
  const [selectedFile, setSelectedFile] = useState(LARAVEL_FILES[4]);
  const [currentUser, setCurrentUser] = useState<User | null>(null);
  const [sessionIp, setSessionIp] = useState<string | null>(null);
  const [sessionCountry, setSessionCountry] = useState<string | null>(null);
  const [sessionUA, setSessionUA] = useState<string | null>(null);
  const [logs, setLogs] = useState<LoginLog[]>([]);
  const [ips, setIps] = useState<LoginIp[]>([]);
  
  const [simUsers, setSimUsers] = useState<User[]>(INITIAL_USERS);
  const [simMode, setSimMode] = useState<'login' | 'register'>('login');
  const [simName, setSimName] = useState('');
  const [simIp, setSimIp] = useState('1.1.1.1');
  const [simUA, setSimUA] = useState<keyof typeof UA_STRINGS>('chrome');
  const [simEmail, setSimEmail] = useState('admin@example.com');
  const [simPassword, setSimPassword] = useState('password123');
  const [simConfirmPassword, setSimConfirmPassword] = useState('password123');
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const [failedAttempts, setFailedAttempts] = useState(0);
  const [cooldownRemaining, setCooldownRemaining] = useState(0);

  useEffect(() => {
    let timer: number;
    if (cooldownRemaining > 0) {
      timer = window.setInterval(() => {
        setCooldownRemaining(prev => prev - 1);
      }, 1000);
    }
    return () => clearInterval(timer);
  }, [cooldownRemaining]);

  // Security Middleware Simulation
  useEffect(() => {
    if (currentUser && (sessionIp || sessionUA)) {
      const currentUAString = UA_STRINGS[simUA];
      let shouldInvalidate = false;
      let reason = "";

      if (sessionUA && sessionUA !== currentUAString) {
        shouldInvalidate = true;
        reason = `Security Alert: Browser or device change detected. Session invalidated for your protection.`;
      }

      if (!shouldInvalidate && sessionIp && sessionIp !== simIp) {
        const currentCountry = MOCK_COUNTRIES[simIp] || 'Unknown Region';
        if (sessionCountry !== currentCountry) {
            shouldInvalidate = true;
            reason = `Security Alert: Significant IP shift from ${sessionCountry} to ${currentCountry}. Session terminated.`;
        }
      }

      if (shouldInvalidate) {
        logActivity(currentUser.id, simIp, 'blocked');
        setCurrentUser(null);
        setSessionIp(null);
        setSessionCountry(null);
        setSessionUA(null);
        setErrorMessage(reason);
      }
    }
  }, [simIp, simUA, currentUser, sessionIp, sessionUA, sessionCountry]);

  const getRecentUniqueIpCount = (email: string) => {
    const user = simUsers.find(u => u.email === email);
    if (!user) return 0;
    const twentyFourHoursAgo = new Date(Date.now() - 24 * 60 * 60 * 1000);
    const recentIpsForUser = ips
      .filter(i => i.userId === user.id && new Date(i.timestamp) > twentyFourHoursAgo)
      .map(i => i.ipAddress);
    return new Set(recentIpsForUser).size;
  };

  const logActivity = (userId: number | null, ip: string, status: 'success' | 'failed' | 'blocked') => {
    setLogs(prev => [{ id: Date.now(), userId, ipAddress: ip, status, timestamp: new Date().toISOString() }, ...prev]);
  };

  const addIpRecord = (userId: number, ip: string) => {
    const country = MOCK_COUNTRIES[ip] || 'Simulated Region';
    setIps(prev => [{ id: Date.now(), userId, ipAddress: ip, userAgent: UA_STRINGS[simUA], country, timestamp: new Date().toISOString() }, ...prev]);
  };

  const handleRegister = () => {
    setErrorMessage(null);
    if (!simName || !simEmail || !simPassword || !simConfirmPassword) {
      setErrorMessage("All fields are required.");
      return;
    }
    if (simPassword !== simConfirmPassword) {
      setErrorMessage("Passwords do not match.");
      return;
    }
    if (simPassword.length < 8) {
      setErrorMessage("Password must be at least 8 characters.");
      return;
    }
    if (simUsers.some(u => u.email === simEmail)) {
      setErrorMessage("Email already registered.");
      return;
    }

    // IP Restriction check even for registration to prevent farm registration
    const uniqueIpCount = getRecentUniqueIpCount(simEmail); 
    if (uniqueIpCount >= 3) {
      logActivity(null, simIp, 'blocked');
      setErrorMessage("Registration blocked: This network has reached the signup limit.");
      return;
    }

    const newUser: User = {
      id: Date.now(),
      name: simName,
      email: simEmail,
      password: simPassword
    };

    setSimUsers(prev => [...prev, newUser]);
    logActivity(newUser.id, simIp, 'success');
    addIpRecord(newUser.id, simIp);
    setCurrentUser(newUser);
    setSessionIp(simIp);
    setSessionCountry(MOCK_COUNTRIES[simIp] || 'Local Access');
    setSessionUA(UA_STRINGS[simUA]);
    setSimMode('login'); // Reset mode for next time
  };

  const handleLogin = () => {
    setErrorMessage(null);
    if (cooldownRemaining > 0) return;

    const user = simUsers.find(u => u.email === simEmail);

    if (!user || user.password !== simPassword) {
      const newFailed = failedAttempts + 1;
      setFailedAttempts(newFailed);
      logActivity(user ? user.id : null, simIp, 'failed');
      if (newFailed >= 5) {
        setCooldownRemaining(60);
        setErrorMessage("Too many login attempts. 60s cooldown applied.");
      } else {
        setErrorMessage(`Invalid credentials. Attempt ${newFailed}/5.`);
      }
      return;
    }

    const oneHourAgo = new Date(Date.now() - 60 * 60 * 1000);
    const wasRecentlyBlocked = logs.some(l => 
      l.userId === user.id && l.ipAddress === simIp && l.status === 'blocked' && new Date(l.timestamp) > oneHourAgo
    );

    if (wasRecentlyBlocked) {
      setErrorMessage("This IP is temporarily restricted (1 hour cooldown) due to previous security blocks.");
      return;
    }

    const uniqueIpCount = getRecentUniqueIpCount(simEmail);
    const isIpNew = !ips.some(i => i.userId === user.id && i.ipAddress === simIp);
    
    if (isIpNew && uniqueIpCount >= 3) {
      logActivity(user.id, simIp, 'blocked');
      setErrorMessage("Login blocked: Maximum 3 unique IPs allowed per 24 hours.");
      return;
    }

    logActivity(user.id, simIp, 'success');
    addIpRecord(user.id, simIp);
    setCurrentUser(user);
    setSessionIp(simIp);
    setSessionCountry(MOCK_COUNTRIES[simIp] || 'Local Access');
    setSessionUA(UA_STRINGS[simUA]);
    setFailedAttempts(0);
  };

  return (
    <div className="min-h-screen flex flex-col font-sans bg-gray-50">
      <header className="bg-white border-b px-6 py-4 flex items-center justify-between sticky top-0 z-50">
        <div className="flex items-center gap-2">
          <ShieldCheck className="text-indigo-600 w-8 h-8" />
          <div>
            <h1 className="text-xl font-bold text-gray-900 leading-tight">Advanced Security PoC</h1>
            <p className="text-[10px] text-gray-500 font-medium uppercase tracking-wider">Strict Session-Geo-Binding</p>
          </div>
        </div>
        <nav className="flex gap-2 p-1 bg-gray-100 rounded-lg">
          <button onClick={() => setActiveTab('simulator')} className={`px-4 py-2 rounded-md text-sm font-semibold transition ${activeTab === 'simulator' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500 hover:text-gray-800'}`}>Live Simulator</button>
          <button onClick={() => setActiveTab('code')} className={`px-4 py-2 rounded-md text-sm font-semibold transition ${activeTab === 'code' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500 hover:text-gray-800'}`}>Project Files</button>
        </nav>
      </header>

      <main className="flex-1 overflow-auto p-6">
        {activeTab === 'simulator' ? (
          <div className="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div className="lg:col-span-4 space-y-6">
              <section className="bg-white p-6 rounded-xl shadow-lg border-t-4 border-indigo-600">
                <div className="flex justify-between items-center mb-6">
                    <h2 className="text-lg font-bold flex items-center gap-2">
                    <Monitor className="w-5 h-5 text-indigo-500" />
                    Env Controls
                    </h2>
                    {currentUser && <span className="bg-green-100 text-green-700 text-[10px] px-2 py-1 rounded-full font-bold uppercase tracking-wide">Connected</span>}
                </div>
                
                <div className="space-y-4">
                  <div className="grid grid-cols-2 gap-2">
                    <div className="col-span-2">
                      <label className="block text-[10px] font-bold text-gray-400 uppercase mb-1">Your IP (Simulated)</label>
                      <div className="flex gap-2">
                        <input type="text" value={simIp} onChange={e => setSimIp(e.target.value)} className={`flex-1 px-4 py-2 border-2 rounded-lg focus:border-indigo-500 outline-none font-mono text-sm transition-colors`} />
                        <button onClick={() => setSimIp(`${Math.floor(Math.random()*255)}.${Math.floor(Math.random()*255)}.${Math.floor(Math.random()*255)}.1`)} className="p-2 border-2 rounded-lg hover:bg-gray-50"><Zap className="w-4 h-4 text-indigo-500" /></button>
                      </div>
                    </div>
                  </div>

                  <div>
                    <label className="block text-[10px] font-bold text-gray-400 uppercase mb-1">Device/Browser</label>
                    <div className="grid grid-cols-3 gap-1">
                      {(Object.keys(UA_STRINGS) as Array<keyof typeof UA_STRINGS>).map(key => (
                        <button key={key} onClick={() => setSimUA(key)} className={`py-1.5 px-1 border-2 rounded-lg text-[10px] font-bold uppercase transition ${simUA === key ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-100 hover:bg-gray-50 text-gray-400'}`}>{key}</button>
                      ))}
                    </div>
                  </div>
                  
                  <hr className="my-6 border-gray-100" />

                  {!currentUser ? (
                    <>
                      <div className="flex bg-gray-100 p-1 rounded-lg mb-4">
                        <button onClick={() => {setSimMode('login'); setErrorMessage(null);}} className={`flex-1 py-1.5 text-[10px] font-bold uppercase rounded-md transition ${simMode === 'login' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:text-gray-700'}`}>Sign In</button>
                        <button onClick={() => {setSimMode('register'); setErrorMessage(null);}} className={`flex-1 py-1.5 text-[10px] font-bold uppercase rounded-md transition ${simMode === 'register' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:text-gray-700'}`}>Register</button>
                      </div>

                      <div className="p-4 bg-gray-50 rounded-xl border-2 border-gray-100 space-y-3 relative overflow-hidden group mb-4">
                        <div className="absolute top-0 right-0 p-2 opacity-10 group-hover:opacity-20 transition-opacity">
                          <Gauge className="w-12 h-12 text-indigo-900" />
                        </div>
                        <div className="flex items-center gap-2 mb-2">
                           <ShieldAlert className="w-4 h-4 text-indigo-600" />
                           <h4 className="text-[10px] font-bold text-gray-800 uppercase tracking-widest">Network Reputation</h4>
                        </div>
                        <div className="flex justify-between items-end">
                          <div>
                            <p className="text-2xl font-black text-indigo-700 leading-none">{getRecentUniqueIpCount(simEmail)}<span className="text-xs text-gray-400 font-medium ml-1">/ 3</span></p>
                            <p className="text-[9px] text-gray-500 font-bold uppercase mt-1">Unique IPs (24h Window)</p>
                          </div>
                          <div className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${getRecentUniqueIpCount(simEmail) >= 3 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'}`}>
                            {getRecentUniqueIpCount(simEmail) >= 3 ? 'RESTRICTED' : 'HEALTHY'}
                          </div>
                        </div>
                        <div className="w-full bg-gray-200 rounded-full h-1 overflow-hidden mt-2">
                          <div 
                            className={`h-full transition-all duration-700 ${getRecentUniqueIpCount(simEmail) >= 3 ? 'bg-red-500' : 'bg-indigo-600'}`} 
                            style={{ width: `${Math.min((getRecentUniqueIpCount(simEmail) / 3) * 100, 100)}%` }}
                          />
                        </div>
                      </div>

                      <div className="space-y-3">
                        {simMode === 'register' && (
                           <input type="text" placeholder="Full Name" value={simName} onChange={e => setSimName(e.target.value)} className="w-full px-4 py-2.5 border-2 rounded-lg focus:border-indigo-500 outline-none text-sm" />
                        )}
                        <input type="email" placeholder="Email Address" value={simEmail} onChange={e => setSimEmail(e.target.value)} className="w-full px-4 py-2.5 border-2 rounded-lg focus:border-indigo-500 outline-none text-sm" />
                        <input type="password" placeholder="Password" value={simPassword} onChange={e => setSimPassword(e.target.value)} className="w-full px-4 py-2.5 border-2 rounded-lg focus:border-indigo-500 outline-none text-sm" />
                        {simMode === 'register' && (
                           <input type="password" placeholder="Confirm Password" value={simConfirmPassword} onChange={e => setSimConfirmPassword(e.target.value)} className="w-full px-4 py-2.5 border-2 rounded-lg focus:border-indigo-500 outline-none text-sm" />
                        )}
                        
                        {errorMessage && <div className="p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-[11px] leading-tight font-bold">{errorMessage}</div>}
                        
                        <button disabled={cooldownRemaining > 0} onClick={simMode === 'login' ? handleLogin : handleRegister} className={`w-full text-white font-bold py-3 rounded-lg transition-all active:scale-[0.98] shadow-lg ${cooldownRemaining > 0 ? 'bg-gray-400' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-100'}`}>
                          {cooldownRemaining > 0 ? `Rate Limited (${cooldownRemaining}s)` : (simMode === 'login' ? 'Secure Login' : 'Create Account')}
                        </button>
                      </div>
                    </>
                  ) : (
                    <div className="space-y-4">
                      <div className="p-4 bg-indigo-900 rounded-xl text-white shadow-xl relative overflow-hidden">
                        <div className="relative z-10">
                          <p className="text-[10px] font-bold text-indigo-300 uppercase mb-2">Authenticated Identity</p>
                          <h3 className="text-xl font-bold mb-0.5">{currentUser.name}</h3>
                          <p className="text-xs text-indigo-200 font-medium mb-1">{currentUser.email}</p>
                          <div className="flex gap-3 opacity-80 mt-4">
                            <div className="text-[10px] font-bold flex items-center gap-1 uppercase"><Globe className="w-3 h-3"/> {sessionCountry}</div>
                            <div className="text-[10px] font-bold flex items-center gap-1 uppercase"><Zap className="w-3 h-3"/> {sessionIp}</div>
                          </div>
                        </div>
                        <ShieldCheck className="absolute -bottom-4 -right-4 w-24 h-24 text-white/5" />
                      </div>
                      
                      <button onClick={() => alert(`Profile Details:\nName: ${currentUser.name}\nEmail: ${currentUser.email}\nSecurity: IP Restricted`)} className="w-full bg-indigo-100 text-indigo-700 hover:bg-indigo-200 font-bold py-2 rounded-lg transition text-xs flex items-center justify-center gap-2">
                        <UserIcon className="w-4 h-4" /> View Profile
                      </button>

                      <button onClick={() => setCurrentUser(null)} className="w-full border-2 border-red-100 text-red-600 hover:bg-red-50 font-bold py-2 rounded-lg transition text-xs flex items-center justify-center gap-2">
                        <LogOut className="w-4 h-4" /> Terminate Session
                      </button>
                    </div>
                  )}
                </div>
              </section>
            </div>

            <div className="lg:col-span-8 space-y-6">
              <section className="bg-white rounded-xl shadow-lg border overflow-hidden">
                <div className="bg-gray-800 px-6 py-4 flex items-center justify-between">
                   <h3 className="text-white font-bold flex items-center gap-2 text-sm"><Activity className="w-4 h-4 text-indigo-400" /> Admin Audit Logs</h3>
                   <span className="text-[9px] text-gray-400 font-mono">RETENTION: 24 HOURS</span>
                </div>
                <div className="max-h-[550px] overflow-auto">
                  <table className="w-full text-left text-sm">
                    <thead className="bg-gray-50 sticky top-0 border-b text-[10px] font-bold uppercase text-gray-400"><tr><th className="px-6 py-3">Time</th><th className="px-6 py-3">User</th><th className="px-6 py-3">Network & Geo</th><th className="px-6 py-3 text-right">Result</th></tr></thead>
                    <tbody className="divide-y divide-gray-100">
                      {logs.length === 0 ? <tr><td colSpan={4} className="px-6 py-12 text-center text-gray-400 italic">No security events detected.</td></tr> : 
                        logs.map(log => {
                          const user = simUsers.find(u => u.id === log.userId);
                          return (
                            <tr key={log.id} className="hover:bg-gray-50/50 transition">
                              <td className="px-6 py-4 font-mono text-[10px] text-gray-400">{new Date(log.timestamp).toLocaleTimeString()}</td>
                              <td className="px-6 py-4"><span className="font-bold block text-gray-900">{user?.name || 'Guest'}</span><span className="text-[9px] text-gray-400 font-mono">{user?.email || 'Anonymous'}</span></td>
                              <td className="px-6 py-4"><div className="flex items-center gap-2"><code className="text-xs bg-gray-100 px-1.5 py-0.5 rounded font-mono font-bold text-indigo-700">{log.ipAddress}</code><span className="text-[10px] text-gray-500 font-bold flex items-center gap-1 uppercase"><Globe className="w-3 h-3" /> {MOCK_COUNTRIES[log.ipAddress] || 'Unknown'}</span></div></td>
                              <td className="px-6 py-4 text-right"><span className={`px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tighter ${log.status === 'success' ? 'bg-green-100 text-green-700' : log.status === 'blocked' ? 'bg-red-600 text-white' : 'bg-amber-100 text-amber-700'}`}>{log.status}</span></td>
                            </tr>
                          );
                        })
                      }
                    </tbody>
                  </table>
                </div>
              </section>
            </div>
          </div>
        ) : (
          <div className="max-w-6xl mx-auto flex gap-6 h-[calc(100vh-140px)]">
            <div className="w-64 bg-white rounded-xl shadow-md border overflow-y-auto p-4 shrink-0">
              <h3 className="text-xs font-bold text-gray-400 uppercase mb-4 px-2">Secure Modules</h3>
              <div className="space-y-1">{LARAVEL_FILES.map(file => (<button key={file.path} onClick={() => setSelectedFile(file)} className={`w-full text-left px-3 py-2 rounded-lg text-xs flex items-center gap-2 transition ${selectedFile.path === file.path ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-gray-600 hover:bg-gray-100'}`}><FileCode className="w-4 h-4 opacity-50" /><span className="truncate">{file.path.split('/').pop()}</span></button>))}</div>
            </div>
            <div className="flex-1 bg-gray-900 rounded-xl shadow-2xl overflow-hidden border border-gray-800 flex flex-col">
              <div className="bg-gray-800 px-4 py-2 flex items-center justify-between"><div className="flex items-center gap-2"><FolderOpen className="w-4 h-4 text-gray-500" /><span className="text-xs font-mono text-indigo-400">/{selectedFile.path}</span></div></div>
              <div className="p-6 overflow-auto font-mono text-sm text-gray-300 leading-relaxed bg-black/50 h-full"><p className="text-indigo-400 mb-4">// Strict Security Service Implementation</p><p className="opacity-70 text-xs leading-loose">This PoC demonstrates a full registration and login flow with IP-based restrictions. Users are blocked if they exceed the 3-IP-per-24h limit or if they attempt to login from a previously blocked IP within a 1-hour cooldown window.</p></div>
            </div>
          </div>
        )}
      </main>
      <footer className="bg-white border-t px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Marketplace Security Layer PoC &bull; V3.0</footer>
    </div>
  );
};

export default App;
